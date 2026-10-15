"""Build one short from a spec file, end to end.
Usage: python3 tools/build_short.py library/<slug>/shorts/<nn>.json [--skip-source] [--no-render]

Spec (all times in LONG-VIDEO seconds, so they can be read straight off transcript.txt):
{
  "slug": "house-pt3", "id": "01", "name": "middleman",
  "from": 1460, "to": 1505,                 # span cut into source.mp4 (must contain every part)
  "parts": [[1460.1, 1471], [1472.7, 1504.6]],  # played in this order (hook first)
  "drops": [[1480.5, 1483.05]],             # retakes / asides to cut
  "cta": "calculator" | "landlord",
  "hook": "On-screen hook title",
  "highlight": ["£250", "middleman"],       # caption words in orange
  "fix": {"shot": "shut"},                  # caption word replacements (by text)
  "fixAt": {"377.16": "it"},                # caption word replacements (by start time)
  "cropX": 1300,                            # optional, otherwise face_x.py decides
  "graphics": [
    {"type": "stat", "at": 1489.3, "until": 1492, "value": 1250, "prefix": "£", "label": "a month"},
    {"type": "list", "at": 503.4, "until": 513.6, "title": "BRR", "items": [{"text": "Buy", "at": 503.5}]},
    {"type": "label", "at": 433.6, "until": 436, "text": "Supply & demand"},
    {"type": "zoom", "at": 439.8, "until": 441.4, "scale": 1.15}
  ]
}
"""
import json, os, re, subprocess, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BROWSER = "/opt/pw-browsers/chromium_headless_shell-1194/chrome-linux/headless_shell"
spec_path = sys.argv[1]
spec = json.load(open(spec_path))
slug, nn, name = spec["slug"], spec["id"], spec["name"]
video = f"input/{slug}/video.mp4"
work = f"work/{slug}/{nn}"
src_mp4 = f"public/{work}/source.mp4"
props_path = f"public/{work}/props.json"
out_mp4 = f"out/{slug}/{nn}-{name}.mp4"
F = spec["from"]
run = lambda *a, **k: subprocess.run(a, cwd=ROOT, check=True, text=True, **k)
os.makedirs(os.path.join(ROOT, f"public/{work}"), exist_ok=True)
os.makedirs(os.path.join(ROOT, f"out/{slug}"), exist_ok=True)

# 1. Crop position (face tracking) and vertical 4K -> 1080x1920 source clip
if "--skip-source" not in sys.argv or not os.path.exists(os.path.join(ROOT, src_mp4)):
    crop = spec.get("cropX")
    if crop is None:
        r = run("python3", "tools/face_x.py", video, str(F), str(spec["to"]), capture_output=True).stdout
        print("\n".join(l for l in r.splitlines() if "CROPX" in l or "MOVES" in l or "No face" in l))
        m = re.search(r"CROPX (\d+)", r) or re.search(r"Centre crop X = (\d+)", r)
        crop = int(m.group(1))
    print(f"cropX {crop}")
    run("tools/make_source.sh", video, str(F), str(spec["to"]), str(crop), src_mp4)

# 2. Cut plan
fmt = lambda rs: ",".join(f"{a}-{b}" for a, b in rs)
args = ["python3", "tools/plan_cuts.py", f"library/{slug}/transcript.json", str(F), str(spec["to"]),
        f"{work}/source.mp4", props_path, f"--cta={spec.get('cta', 'calculator')}"]
if spec.get("parts"):
    args.append(f"--parts={fmt(spec['parts'])}")
if spec.get("drops"):
    args.append(f"--drop={fmt(spec['drops'])}")
run(*args)
props = json.load(open(os.path.join(ROOT, props_path)))

# 3. Caption clean-up: £ not $, glue split tokens ("1" ".5", "70" "%", "re" "-mortgage")
fix_at = {round(float(k), 2): v for k, v in spec.get("fixAt", {}).items()}
words = []
for w in props["words"]:
    t = w["text"].replace("$", "£")
    if round(w["start"] + F, 2) in fix_at:
        t = fix_at[round(w["start"] + F, 2)]
    t = spec.get("fix", {}).get(t, t)
    if words and re.match(r"^[.,%\-]\S", t) or (words and t == "%"):
        words[-1]["text"] += t
        words[-1]["end"] = w["end"]
        continue
    words.append({**w, "text": t})
props["words"] = words


# 4. Map long-video times to the output timeline
def out_t(x):
    x -= F
    off, best = 0.0, None
    for s in props["segments"]:
        if s["from"] <= x < s["to"]:
            return round(off + x - s["from"], 3)
        if s["from"] > x and (best is None or s["from"] - x < best[0]):
            best = (s["from"] - x, off)
        off += s["to"] - s["from"]
    return round(best[1], 3) if best else round(off, 3)


graphics = []
for g in spec.get("graphics", []):
    g = dict(g)
    at, until = out_t(g.pop("at")), out_t(g.pop("until"))
    g["at"], g["dur"] = at, max(round(until - at, 3), 0.8)
    if "items" in g:
        g["items"] = [{"text": i["text"], "at": out_t(i["at"])} for i in g["items"]]
    graphics.append(g)
props.update(hook=spec.get("hook", ""), highlight=spec.get("highlight", []), graphics=graphics)
json.dump(props, open(os.path.join(ROOT, props_path), "w"), indent=1)
total = sum(s["to"] - s["from"] for s in props["segments"]) + (2 if props["endCard"] else 0)
print(f"output {total:.1f}s, graphics at " + ", ".join(f"{g['type']}@{g['at']}" for g in graphics))
if "--no-render" in sys.argv:
    sys.exit()

# 5. Render + checks
run("npx", "remotion", "render", "TalkingShort", out_mp4, f"--props={props_path}", "--codec", "h264",
    "--crf", "18", "--browser-executable", BROWSER, "--log", "error")
black = subprocess.run(["ffmpeg", "-i", out_mp4, "-vf", "blackdetect=d=0.1", "-an", "-f", "null", "-"],
                       cwd=ROOT, capture_output=True, text=True).stderr.count("black_start")
lufs = re.findall(r"I:\s+(-?[\d.]+) LUFS", subprocess.run(
    ["ffmpeg", "-i", out_mp4, "-af", "ebur128", "-f", "null", "-"], cwd=ROOT, capture_output=True, text=True).stderr)
lufs = float(lufs[-1]) if lufs else None
if lufs is not None and abs(lufs + 14) > 1:
    tmp = out_mp4.replace(".mp4", ".tmp.mp4")
    run("ffmpeg", "-v", "error", "-y", "-i", out_mp4, "-c:v", "copy", "-af", "loudnorm=I=-14:TP=-1.5:LRA=11",
        "-c:a", "aac", "-b:a", "192k", tmp)
    os.replace(os.path.join(ROOT, tmp), os.path.join(ROOT, out_mp4))
    print(f"loudness was {lufs} LUFS, normalised to -14")
run("tools/contact_sheet.sh", out_mp4, f"out/{slug}/{nn}-{name}-sheet.jpg", "1")
print(f"DONE {out_mp4}  black frames: {black}  loudness: {lufs} LUFS")
