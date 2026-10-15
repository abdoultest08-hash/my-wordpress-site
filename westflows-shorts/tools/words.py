"""Print words with start times in a range, to pick exact cut points.
Usage: python3 words.py TRANSCRIPT.json FROM TO"""
import json, sys
a, b = float(sys.argv[2]), float(sys.argv[3])
line = []
for w in json.load(open(sys.argv[1]))["words"]:
    if a <= w["start"] <= b:
        line.append(f"{w['text']}@{w['start']:.2f}")
        if w["text"][-1:] in ".?!" or len(line) > 14:
            print(" ".join(line)); line = []
print(" ".join(line))
