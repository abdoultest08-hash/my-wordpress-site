"""Turn a moment into Remotion props: keep speech, cut pauses, fillers, stutters and bad takes.
Usage: python3 plan_cuts.py TRANSCRIPT.json FROM TO SRC_IN_PUBLIC OUT_PROPS.json
         [--no-trim] [--parts=A-B,C-D] [--drop=A-B,C-D] [--cta=calculator|landlord]
  --parts  play these ranges (long-video seconds, inside FROM-TO) in this order. Use it to open
           on the hook line, then go back to the build-up. Default: the whole FROM-TO.
  --drop   cut these ranges (long-video seconds), e.g. a false start, a retake or an aside
  --cta    sets the end card text (calculator: numbers / finance, landlord: SA / landlord content)
Times in the output are relative to source.mp4 (which starts at FROM).
Prints every cut so it can be checked against the transcript.
Note: Whisper often leaves "um / uh" out of the transcript. Those show up as gaps and the
pause rule removes them."""
import json, re, sys

tr_path, start, end, src, out = sys.argv[1], float(sys.argv[2]), float(sys.argv[3]), sys.argv[4], sys.argv[5]
opts = dict(a[2:].split("=", 1) for a in sys.argv[6:] if a.startswith("--") and "=" in a)
no_trim = "--no-trim" in sys.argv
rng = lambda key: [tuple(map(float, r.split("-"))) for r in opts.get(key, "").split(",") if r]
drops, parts = rng("drop"), rng("parts") or [(start, end)]
cta = opts.get("cta", "calculator")
GAP, PAD = 0.35, 0.08
FILLERS = {"um", "umm", "uhm", "uh", "uhh", "erm", "er", "ah", "eh", "hmm", "mm", "mhm"}
END_CARD = {
    "calculator": "Free deal calculator: link in bio",
    "landlord": "Landlords: see how it works. Link in bio",
}
norm = lambda t: re.sub(r"[^a-z0-9£%']", "", t.lower())
all_words = json.load(open(tr_path))["words"]


def plan_part(a, b):
    """Kept segments (long-video seconds) and kept words for one range."""
    words = [w for w in all_words if w["start"] >= a and w["end"] <= b + 0.05]
    why = [None] * len(words)
    for i, w in enumerate(words):
        n = norm(w["text"])
        if n in FILLERS:
            why[i] = "filler"
        elif w["text"].rstrip().endswith("-") or not n:
            why[i] = "false start"
        elif any(x <= w["start"] < y for x, y in drops):
            why[i] = "dropped range"
    # Stutters / repeats: "I I think", "it was it was" -> keep the second, cleaner one
    for n in (3, 2, 1):
        for i in range(len(words) - 2 * n + 1):
            idx = [j for j in range(i, len(words)) if why[j] is None][: 2 * n]
            if len(idx) < 2 * n or idx[0] != i:
                continue
            x = [norm(words[j]["text"]) for j in idx[:n]]
            y = [norm(words[j]["text"]) for j in idx[n:]]
            if x == y and words[idx[n]]["start"] - words[idx[n - 1]]["end"] < 1.0:
                for j in idx[:n]:
                    why[j] = "repeat"
    if no_trim:
        return [[a, b]], [w for w, r in zip(words, why) if r != "filler"]
    keep, segs = [], []
    floor, force = a, False  # floor: never start a segment inside a cut word
    for w, r in zip(words, why):
        if r:
            force = True
            floor = max(floor, w["end"])
            if segs:  # stop the previous segment's padding bleeding into the cut word
                segs[-1][1] = min(segs[-1][1], w["start"])
            print(f"  cut {r:13s} {w['start']:8.2f}  {w['text']}")
            continue
        keep.append(w)
        s, e = max(w["start"] - PAD, floor, a), min(w["end"] + PAD, b)
        if segs and not force and s - segs[-1][1] <= GAP:
            segs[-1][1] = max(segs[-1][1], e)
        else:
            segs.append([s, e])
        force = False
    return [s for s in segs if s[1] - s[0] > 0.05], keep


segs, keep = [], []
for a, b in parts:
    s, k = plan_part(max(a, start), min(b, end))
    segs += s
    keep += k

props = {
    "src": src,
    "segments": [{"from": round(s - start, 3), "to": round(e - start, 3)} for s, e in segs],
    "words": [{"text": w["text"], "start": round(w["start"] - start, 3), "end": round(w["end"] - start, 3)}
              for w in keep],
    "hook": "TODO",
    "highlight": [],
    "broll": [],
    "graphics": [],
    "music": None,
    "musicVolume": 0.08,
    "endCard": False,  # references have no end card; CTA goes in the post caption
    "endCardText": END_CARD[cta],
}
json.dump(props, open(out, "w"), indent=1)
kept = sum(s["to"] - s["from"] for s in props["segments"])
print(f"{len(segs)} segments, {kept:.1f}s kept, {len(keep)} words")
