"""Turn a moment into Remotion props: keep speech, cut pauses, fillers, stutters and bad takes.
Usage: python3 plan_cuts.py TRANSCRIPT.json FROM TO SRC_IN_PUBLIC OUT_PROPS.json
         [--no-trim] [--drop=A-B,C-D] [--cta=calculator|landlord]
  --drop   cut these ranges (long-video seconds), e.g. a false start or a retaken sentence
  --cta    sets the end card text (calculator: numbers / finance, landlord: SA / landlord content)
Times in the output are relative to source.mp4 (which starts at FROM).
Prints every cut so it can be checked against the transcript."""
import json, re, sys

tr_path, start, end, src, out = sys.argv[1], float(sys.argv[2]), float(sys.argv[3]), sys.argv[4], sys.argv[5]
opts = dict(a[2:].split("=", 1) for a in sys.argv[6:] if a.startswith("--") and "=" in a)
no_trim = "--no-trim" in sys.argv
drops = [tuple(map(float, r.split("-"))) for r in opts.get("drop", "").split(",") if r]
cta = opts.get("cta", "calculator")
GAP, PAD = 0.35, 0.08
FILLERS = {"um", "umm", "uhm", "uh", "uhh", "erm", "er", "ah", "eh", "hmm", "mm", "mhm"}
END_CARD = {
    "calculator": "Free deal calculator: link in bio",
    "landlord": "Landlords: see how it works. Link in bio",
}

norm = lambda t: re.sub(r"[^a-z0-9£%']", "", t.lower())
words = [w for w in json.load(open(tr_path))["words"] if w["start"] >= start and w["end"] <= end]

# Decide why each word is cut (None = keep)
why = [None] * len(words)
for i, w in enumerate(words):
    n = norm(w["text"])
    if n in FILLERS:
        why[i] = "filler"
    elif w["text"].rstrip().endswith("-") or not n:
        why[i] = "false start"
    elif any(a <= w["start"] < b for a, b in drops):
        why[i] = "dropped range"
# Stutters / repeats: "I I think", "it was it was" -> keep the second, cleaner one
for n in (3, 2, 1):
    for i in range(len(words) - 2 * n + 1):
        idx = [j for j in range(i, len(words)) if why[j] is None][: 2 * n]
        if len(idx) < 2 * n or idx[0] != i:
            continue
        a = [norm(words[j]["text"]) for j in idx[:n]]
        b = [norm(words[j]["text"]) for j in idx[n:]]
        if a == b and words[idx[n]]["start"] - words[idx[n - 1]]["end"] < 1.0:
            for j in idx[:n]:
                why[j] = "repeat"

if no_trim:
    segs = [[0.0, end - start]]
    keep = [w for w, r in zip(words, why) if r != "filler"]
else:
    keep, segs = [], []
    floor, force = start, False  # floor: never start a segment inside a cut word
    for w, r in zip(words, why):
        if r:
            force = True
            floor = max(floor, w["end"])
            if segs:  # stop the previous segment's padding bleeding into the cut word
                segs[-1][1] = min(segs[-1][1], w["start"] - start)
            print(f"  cut {r:13s} {w['start']:8.2f}  {w['text']}")
            continue
        keep.append(w)
        s, e = max(w["start"] - PAD, floor, start) - start, min(w["end"] + PAD, end) - start
        if segs and not force and s - segs[-1][1] <= GAP:
            segs[-1][1] = max(segs[-1][1], e)
        else:
            segs.append([s, e])
        force = False
    segs = [s for s in segs if s[1] - s[0] > 0.05]

props = {
    "src": src,
    "segments": [{"from": round(s, 3), "to": round(e, 3)} for s, e in segs],
    "words": [{"text": w["text"], "start": round(w["start"] - start, 3), "end": round(w["end"] - start, 3)}
              for w in keep],
    "hook": "TODO",
    "broll": [],
    "graphics": [],
    "music": None,
    "musicVolume": 0.08,
    "endCard": True,
    "endCardText": END_CARD[cta],
}
json.dump(props, open(out, "w"), indent=1)
kept = sum(s["to"] - s["from"] for s in props["segments"])
print(f"{len(segs)} segments, {kept:.1f}s kept of {end - start:.1f}s, "
      f"{sum(1 for r in why if r)} words cut")
if kept > 58:
    print("WARNING: over 58s once the 2s end card is added. Trim the moment or drop a sentence.")
