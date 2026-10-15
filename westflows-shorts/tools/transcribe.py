"""Transcribe with word timings.
Usage: python3 transcribe.py AUDIO OUT.json [model]   (default small.en; try medium.en if poor)
Also writes OUT.txt: one line per sentence with [mm:ss] for reading and shortlisting.
The prompt includes fillers on purpose: Whisper copies the prompt's style, so it writes down
"um", "uh", "erm" instead of silently skipping them, which lets plan_cuts.py cut them out."""
import json, sys
from faster_whisper import WhisperModel

src, out = sys.argv[1], sys.argv[2]
size = sys.argv[3] if len(sys.argv) > 3 else "small.en"
model = WhisperModel(size, device="cpu", compute_type="int8")
segs, _ = model.transcribe(
    src, language="en", word_timestamps=True, vad_filter=True,
    initial_prompt=("Umm, so, uh, I- I bought my first, erm, buy-to-let. Like, you know, the BTL, "
                    "HMO, BRR, serviced accommodation, SA, SDLT, stamp duty, remortgage, LTV, "
                    "bridging, Article 4, consent to let, West Flows."),
)
sentences, words = [], []
for s in segs:
    sentences.append({"start": round(s.start, 2), "end": round(s.end, 2), "text": s.text.strip()})
    for w in s.words or []:
        words.append({"text": w.word.strip(), "start": round(w.start, 3),
                      "end": round(w.end, 3), "p": round(w.probability, 2)})
json.dump({"sentences": sentences, "words": words}, open(out, "w"), indent=1)
with open(out.rsplit(".", 1)[0] + ".txt", "w") as f:
    for s in sentences:
        m, sec = divmod(int(s["start"]), 60)
        f.write(f"[{m:02d}:{sec:02d}] ({s['start']}) {s['text']}\n")
print(f"{len(sentences)} sentences, {len(words)} words")
