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
run = lambda *a, **k: subprocess.run(a, cwd=ROOT, check=True, text=True, **k)
os.makedirs(os.path.join(ROOT, f"public/{work}"), exist_ok=True)
os.makedirs(os.path.join(ROOT, f"out/{slug}"), exist_ok=True)

# Only the footage the short uses is cut from the 4K file (slicing 4K HEVC is ~8x slower than
# real time). Blocks = the parts, padded and merged, laid end to end in source.mp4.
PAD = 0.3
ALL_WORDS = json.load(open(os.path.join(ROOT, f"library/{slug}/transcript.json")))["words"]


def whole_words(a, b):
    """Widen a range so it never starts or ends part-way through a word."""
    for w in ALL_WORDS:
        if w["start"] < a < w["end"]:
            a = w["start"] - 0.02
        if w["start"] < b < w["end"]:
            b = w["end"] + 0.02
    return [round(a, 3), round(b, 3)]


if spec.get("parts"):
    spec["parts"] = [whole_words(a, b) for a, b in spec["parts"]]
blocks = []
for a, b in sorted(spec.get("parts") or [[spec["from"], spec["to"]]]):
    a, b = max(a - PAD, 0), b + PAD
    if blocks and a <= blocks[-1][1]:
        blocks[-1][1] = max(blocks[-1][1], b)
    else:
        blocks.append([a, b])
offsets, off = [], 0.0
for a, b in blocks:
    offsets.append(off)
    off += b - a
source_len = round(off, 3)


def local(x):
    """Long-video seconds -> source.mp4 seconds."""
    for (a, b), o in zip(blocks, offsets):
        if a <= x <= b:
            return round(o + x - a, 3)
    later = [o for (a, b), o in zip(blocks, offsets) if a > x]
    return later[0] if later else source_len


# 1. Face-tracked vertical crop per block, then join into one 1080x1920 source clip
if "--skip-source" not in sys.argv or not os.path.exists(os.path.join(ROOT, src_mp4)):
    pieces = []
    for k, (a, b) in enumerate(blocks):
        crop = spec.get("cropX")
        if crop is None:
            r = run("python3", "tools/face_x.py", video, str(a), str(b), capture_output=True).stdout
            m = re.search(r"CROPX (\d+)", r) or re.search(r"Centre crop X = (\d+)", r)
            crop = int(m.group(1))
            if "MOVES" in r:
                print(f"  block {a:.1f}-{b:.1f}: face moves, check framing")
        print(f"  block {a:.1f}-{b:.1f} cropX {crop}")
        piece = f"public/{work}/part{k}.mp4"
        run("tools/make_source.sh", video, f"{a:.3f}", f"{b:.3f}", str(crop), piece)
        pieces.append(os.path.basename(piece))
    with open(os.path.join(ROOT, f"public/{work}/parts.txt"), "w") as f:
        f.writelines(f"file '{p}'\n" for p in pieces)
    run("ffmpeg", "-v", "error", "-y", "-f", "concat", "-safe", "0", "-i", f"public/{work}/parts.txt",
        "-c", "copy", src_mp4)

# 2. Cut plan, on a transcript moved onto the source.mp4 timeline (word fixes by time applied here)
fix_at = {round(float(k), 2): v for k, v in spec.get("fixAt", {}).items()}
tr = json.load(open(os.path.join(ROOT, f"library/{slug}/transcript.json")))
local_words = []
for (a, b), o in zip(blocks, offsets):
    for w in tr["words"]:
        if a <= w["start"] and w["end"] <= b:
            t = fix_at.get(round(w["start"], 2), w["text"])
            local_words.append({**w, "text": t, "start": round(o + w["start"] - a, 3),
                                "end": round(o + w["end"] - a, 3)})
local_tr = f"public/{work}/transcript.local.json"
json.dump({"words": local_words}, open(os.path.join(ROOT, local_tr), "w"))
fmt = lambda rs: ",".join(f"{local(a)}-{local(b)}" for a, b in rs)
args = ["python3", "tools/plan_cuts.py", local_tr, "0", str(source_len),
        f"{work}/source.mp4", props_path, f"--cta={spec.get('cta', 'calculator')}"]
if spec.get("parts"):
    args.append(f"--parts={fmt(spec['parts'])}")
if spec.get("drops"):
    args.append(f"--drop={fmt(spec['drops'])}")
run(*args)
props = json.load(open(os.path.join(ROOT, props_path)))

# 3. Caption clean-up: £ not $, glue split tokens ("1" ".5", "70" "%", "re" "-mortgage")
words = []
for w in props["words"]:
    t = w["text"].replace("$", "£")
    t = spec.get("fix", {}).get(t, t)
    if words and re.match(r"^[.,%\-]\S", t) or (words and t == "%"):
        words[-1]["text"] += t
        words[-1]["end"] = w["end"]
        continue
    words.append({**w, "text": t})
props["words"] = words


# 4. Map long-video times to the output timeline
def out_t(x, end=False):
    """end=True: x is when something should disappear, so a time just after a cut snaps back
    to the end of the segment before it instead of jumping to whatever plays next."""
    x = local(x)
    off, best = 0.0, None
    for s in props["segments"]:
        if s["from"] <= x < s["to"] or (end and s["from"] < x <= s["to"] + 0.5):
            return round(off + min(x, s["to"]) - s["from"], 3)
        if s["from"] > x and (best is None or s["from"] - x < best[0]):
            best = (s["from"] - x, off)
        off += s["to"] - s["from"]
    return round(best[1], 3) if best else round(off, 3)


def card_top(t):
    """Stat / list card position: on the chest in close shots, above the head in wide shots
    (in a wide shot the face sits around 45% down the frame, where the card would cover it)."""
    import cv2, numpy as np
    raw = subprocess.run(["ffmpeg", "-v", "error", "-ss", f"{t + 0.3:.2f}", "-i", video, "-frames:v", "1",
                          "-vf", "scale=960:540", "-f", "rawvideo", "-pix_fmt", "bgr24", "-"],
                         cwd=ROOT, capture_output=True).stdout
    det = cv2.FaceDetectorYN.create(os.path.join(ROOT, "tools/models/yunet.onnx"), "", (960, 540), 0.7)
    faces = det.detect(np.frombuffer(raw, np.uint8).reshape(540, 960, 3))[1]
    if faces is None:
        return None
    x, y, w, h = max(faces, key=lambda r: r[2] * r[3])[:4]
    top, chin = y * 1920 / 540, (y + h) * 1920 / 540  # face edges in output pixels
    if top > 650:  # wide shot: the card (up to ~410px tall) fits above the head
        return 300
    return max(860, int(chin) + 30)  # close shot: just under the chin


graphics = []
for g in spec.get("graphics", []):
    g = dict(g)
    if g["type"] in ("stat", "list") and "top" not in g:
        top = card_top(g["at"])
        if top is not None:
            g["top"] = top
    at, until = out_t(g.pop("at")), out_t(g.pop("until"), end=True)
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
