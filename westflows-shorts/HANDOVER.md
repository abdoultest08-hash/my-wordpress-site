# West Flows Shorts: long video to vertical shorts with Claude Code + Remotion

Handover for a new Claude Code session. Built from the workflow we used for the Céleste fashion
edits, rewritten for the West Flows YouTube channel (UK property investment, "Property Done
Differently").

**Claude: read this whole file first, then follow "Section 3: First session setup".**

---

## 1. What this project does

1. Studies my existing shorts in `reference/` and writes down the house style (`library/STYLE.md`).
2. Takes a long talking video I drop into `input/<video-slug>/`.
3. Transcribes it with word timings, finds the best 20-59s moments and shows me a shortlist.
4. For each moment I pick: crops to vertical following my face, cuts out pauses and "erm"s,
   burns in captions, adds a hook title, optional B-roll, music bed and end card.
5. Renders 1080x1920 MP4s plus a text file per short with titles and the Instagram caption.

Media never goes in Git. Code, transcripts, catalogues and the style guide do.

## 2. Folder layout

```
westflows-shorts/
  HANDOVER.md              this file
  reference/               my existing shorts (mp4). Read-only. Style source.
  input/<video-slug>/      the long video, e.g. input/btl-mortgages/video.mp4
  brand/                   logo.png, fonts, any brand stills (optional)
  library/
    STYLE.md               house style, written from reference/ (in Git)
    reference/             contact sheets + notes per reference short (in Git)
    <video-slug>/          transcript.json, transcript.txt, sheet.jpg, moments.json (in Git)
  public/                  Remotion static root
    brand/                 copied from brand/ (fonts, logo)
    broll/                 property photos, charts, screenshots
    work/<slug>/<nn>/      source.mp4 + props.json per short (generated, not in Git)
  src/                     Remotion code (TalkingShort template)
  tools/                   transcribe.py, face_x.py, make_source.sh, plan_cuts.py, contact_sheet.sh
  out/<slug>/              finished MP4s + caption .txt files (not in Git)
```

`.gitignore`:
```
input/
reference/
public/work/
out/
node_modules/
*.mp4
*.mov
*.wav
*.m4a
*.zip
```
(`reference/` is ignored because it is media; the analysis lives in `library/reference/`.)

## 3. First session setup (Claude does this once)

1. Scaffold if `package.json` does not exist: `npx create-video@latest --yes --blank .`
   (if it refuses a non-empty folder, scaffold into `tmp/` and move the files up).
2. `npx remotion add @remotion/fonts`
3. `pip install faster-whisper opencv-python-headless numpy --break-system-packages`
   (drop the flag if not needed locally).
4. Check `ffmpeg -version`. Install if missing.
5. Create the tools from Appendix B and the Remotion files from Appendix A. `chmod +x tools/*.sh`.
6. Fonts: put `Anton-Regular.ttf` and `Inter` (ExtraBold or the variable font) in `public/brand/fonts/`.
   Source: github.com/google/fonts (`ofl/anton`, `ofl/inter`). Swap for whatever the references use.
7. Render the example composition once to prove the toolchain works.
8. Do **Section 4** (reference analysis). Do not cut any shorts until STYLE.md is agreed.

### Environment notes

- **Local Claude Code (desktop / terminal)**: files are already on disk, just use the folders.
  Remotion downloads its own Chrome normally.
- **Claude Code cloud sessions**:
  - Media arrives by a shared link. Dropbox shared folder with `dl=1` = one zip of the whole
    folder (`curl -L -o all.zip "<link>"`, `unzip`, delete the zip). Big folders can fail to zip:
    share per subfolder or per file. Quote paths with spaces.
  - Network settings must allow the file host (`www.dropbox.com`, `dl.dropboxusercontent.com`,
    `*.dropboxusercontent.com`, `drive.google.com`, `drive.usercontent.google.com`) with
    "Allow package managers" ticked.
  - Remotion's Chrome download is blocked. Use the pre-installed one:
    `--browser-executable /opt/pw-browsers/chromium_headless_shell-*/chrome-linux/headless_shell`
    (check the real folder with `ls /opt/pw-browsers`).
  - About 30 GB disk. A 20-min 4K file is about 7 GB: extract audio, make the short sources,
    then delete the original if space is tight. Sessions are temporary: media must be
    re-downloaded next time; everything in Git survives.
- **Claude cannot hear audio.** Speech comes from the transcript. I check music feel and voice quality.
- If TypeScript rejects `trimBefore` on `OffthreadVideo`, the Remotion version is older: use `startFrom`.

## 4. Reference analysis (once, then whenever I add new references)

For every file in `reference/`:

1. `ffprobe` duration, resolution, fps.
2. Contact sheet at 2 fps: `tools/contact_sheet.sh ref.mp4 library/reference/<name>.jpg 0.5`
3. Cut rhythm: `ffmpeg -i ref.mp4 -vf "select='gt(scene,0.25)',showinfo" -an -f null - 2>&1 | grep pts_time`
   (count cuts per 10s, note jump cuts vs B-roll cutaways).
4. Pull 3-4 full-size frames (`ffmpeg -ss T -i ref.mp4 -frames:v 1 f.png`) to read caption detail.

Then write `library/STYLE.md` covering:

| Area | What to record |
|---|---|
| Length | typical and max duration |
| Framing | how tight the crop is, where my face sits, any punch-in zooms |
| Captions | font, size, case, colour, highlight colour, stroke/shadow, words per line, position (px from top) |
| Hook | is there an on-screen title, how long, style, wording pattern |
| Pace | cuts per 10s, pauses trimmed or not, jump cuts |
| B-roll | how often, what kind (property photos, numbers, screenshots) |
| Music | present or not, rough level under voice |
| Ending | end card, CTA wording, logo |
| Colour | graded or natural |

Update `src/brand.ts` to match STYLE.md. Show me STYLE.md plus one test render on a 10s piece of
the first long video before batch work. Once I approve it, later videos skip this step.

Where references and the brand defaults below disagree, **references win**.

### Brand defaults (use if references do not show something)

- Accent `#FF8D48` (orange), near-black `#17181c`, white.
- Fonts: Anton for captions and hook, Inter for small text, Source Serif 4 for any serif accent.
- British English. No em dashes anywhere on screen or in captions. Money as £.
- CTA mapping: finance / numbers / mortgages / deal analysis content → deal calculator;
  serviced accommodation / rent-to-SA / landlord content → landlord page. Always "link in bio".
- Copy sounds like I talk: conversational, not corporate, no "content marketer" phrasing.

## 5. Per long video workflow

Say the slug, e.g. `btl-mortgages`.

1. **Audio out**: `ffmpeg -i input/<slug>/video.mp4 -vn -ac 1 -ar 16000 library/<slug>/audio.wav`
   (audio.wav is gitignored by `*.wav`).
2. **Transcribe**: `python3 tools/transcribe.py library/<slug>/audio.wav library/<slug>/transcript.json`
   Uses `small.en` on CPU (a few minutes for 20 min). Use `medium.en` if the transcript is poor.
   Fix obvious mishearings of property terms (BTL, HMO, BRR, SDLT, LTV, SA, Article 4, £ amounts).
3. **Visual scan**: `tools/contact_sheet.sh input/<slug>/video.mp4 library/<slug>/sheet.jpg 5`
   Note where I move, stand up, walk off, hold something up, or the shot changes.
4. **Shortlist 6-10 moments**, 20-59s each. A good moment has:
   - a strong first sentence that works with zero context (the hook),
   - one clear idea,
   - a payoff (a number, a rule, a mistake to avoid, a clear opinion),
   - no "as I said earlier", "this one here", or pointing at things off screen.
   Show me a table: `id | from-to | length | first line (hook) | idea | payoff | CTA | score /10`.
   Save to `library/<slug>/moments.json` (Appendix C). **Wait for my pick** ("1, 3, 4" or "all").
5. For each picked moment:
   1. **Crop position**: `python3 tools/face_x.py input/<slug>/video.mp4 FROM TO`
      Gives the crop X. If it says MOVES, split the moment at a sentence boundary and use a
      different X per part (reads as a natural jump cut).
   2. **Source clip**: `tools/make_source.sh input/<slug>/video.mp4 FROM TO CROPX public/work/<slug>/<nn>/source.mp4`
      (vertical crop from the 4K frame, 1080x1920, voice cleaned and set to -14 LUFS).
   3. **Cut plan + props**: `python3 tools/plan_cuts.py library/<slug>/transcript.json FROM TO work/<slug>/<nn>/source.mp4 public/work/<slug>/<nn>/props.json`
      Removes gaps over 0.35s and filler words. Then edit `props.json`: set `hook`, add `broll`,
      `music`, `endCardText`. Hook text: short, punchy, max ~7 words, not a copy of the first line.
   4. **Render**:
      `npx remotion render TalkingShort out/<slug>/<nn>-<short-name>.mp4 --props=public/work/<slug>/<nn>/props.json --codec h264 --crf 18`
      (add `--browser-executable ...` in cloud sessions).
   5. **Check** (Section 6). Fix and re-render before showing me.
   6. **Caption file** `out/<slug>/<nn>-<short-name>.txt` (Section 7).
6. Render the first picked short, show it, wait for notes. Then batch the rest.
7. Update `moments.json` statuses, commit code + library, push.

## 6. Checks before sending a short

- Contact sheet of the render at 4 fps: face in frame, nothing cropped badly, captions readable,
  captions not over my mouth/eyes, no blank or black frames.
- `ffmpeg -i out.mp4 -vf blackdetect=d=0.1 -an -f null - 2>&1 | grep black_start` → nothing.
- `ffmpeg -i out.mp4 -af ebur128 -f null - 2>&1 | tail -12` → integrated about -14 LUFS (±1).
  If off: `ffmpeg -i out.mp4 -c:v copy -af loudnorm=I=-14:TP=-1.5:LRA=11 -c:a aac -b:a 192k fixed.mp4`
- Duration under 60s. First frame is a good still (it is the default thumbnail).
- Captions match what I say, British spelling, no em dashes, figures correct.
- Jump cuts: if a cut lands mid-word or looks like a glitch, widen the segment in props.json.
- Caption safe zone (TikTok / Reels UI): keep text out of the top ~220px, bottom ~380px,
  right ~140px.

## 7. Caption file per short

```
ON-SCREEN HOOK:
<hook>

YOUTUBE SHORTS TITLE:
<under 60 characters, no clickbait>

INSTAGRAM CAPTION:
<hook headline with one emoji>

<2-4 sentences explaining the idea in plain English>

→ <bullet only if genuinely listing>
→ <bullet>

<one sentence pointing to the deal calculator OR landlord page, saying what it does and why it helps>

Link in bio 👆

CTA TARGET: calculator | landlord
```

Rules: British English, no em dashes, sounds like me, no details from other videos.

## 8. Lessons carried over

- Look at everything before planning. Contact sheets are how Claude "watches".
- Hook in the first second. If the moment's first sentence is weak, start later or rely on the hook title.
- 4K source means a vertical crop is still sharp at 1080x1920 (1216x2160 slice). Punch-ins up
  to ~1.3x stay clean; more than that goes soft.
- Social apps re-compress: deliver 1080x1920 H.264, CRF 18, 30fps.
- Music: no commercial tracks baked in (muted/blocked). Use YouTube Audio Library, Pixabay,
  Uppbeat or Epidemic, or post without music and add sound in the app. Bed about -20 dB under voice
  (`musicVolume` 0.06-0.1).
- Grade lightly or not at all on talking heads: skin tones go wrong fast. Test on 3 frames
  side by side against a reference before applying.
- Check every render frame by frame. It caught a light stand, a pole and a walk-off on the fashion edits.

---

## Appendix A: Remotion code

### `src/brand.ts`

```ts
import { loadFont } from "@remotion/fonts";
import { staticFile } from "remotion";

// Defaults. Overwrite from library/STYLE.md after reference analysis.
export const brand = {
  accent: "#FF8D48",
  ink: "#17181c",
  white: "#ffffff",
  captionFont: "Anton",
  captionSize: 96,
  captionTop: 1130, // px from top, inside the safe zone
  captionColour: "#ffffff",
  captionStroke: 10,
  wordsPerPage: 3,
  uppercase: true,
  hookFont: "Anton",
  hookSize: 78,
  hookTop: 260,
  hookBg: "#FF8D48",
  hookColour: "#17181c",
  hookSeconds: 2.2,
  bodyFont: "Inter",
  endBg: "#ffffff",
  logo: "", // e.g. "brand/logo.png" once added to public/brand/
  endCardText: "Free deal calculator: link in bio",
};

loadFont({ family: "Anton", url: staticFile("brand/fonts/Anton-Regular.ttf") });
loadFont({ family: "Inter", url: staticFile("brand/fonts/Inter.ttf"), weight: "800" });
```

### `src/TalkingShort/TalkingShort.tsx`

```tsx
import { useMemo } from "react";
import {
  AbsoluteFill,
  Audio,
  Img,
  interpolate,
  OffthreadVideo,
  Sequence,
  spring,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { brand } from "../brand";

export type Word = { text: string; start: number; end: number }; // seconds in source.mp4
export type Segment = { from: number; to: number }; // kept ranges in source.mp4
export type Broll = { src: string; at: number; dur: number }; // seconds on the OUTPUT timeline

export type TalkingShortProps = {
  src: string; // inside public/, e.g. "work/btl-mortgages/01/source.mp4"
  segments: Segment[];
  words: Word[];
  hook?: string;
  broll?: Broll[];
  music?: string | null;
  musicVolume?: number;
  endCard?: boolean;
  endCardText?: string;
};

export const END_CARD_SECONDS = 2;
const clamp = { extrapolateLeft: "clamp", extrapolateRight: "clamp" } as const;

export const speechSeconds = (segments: Segment[]) =>
  segments.reduce((a, s) => a + (s.to - s.from), 0);

export const totalSeconds = (p: TalkingShortProps) =>
  speechSeconds(p.segments) + (p.endCard ? END_CARD_SECONDS : 0);

// Move word timings from source.mp4 onto the output timeline (after pauses are cut out)
const mapWords = (words: Word[], segments: Segment[]): Word[] => {
  const out: Word[] = [];
  let offset = 0;
  for (const s of segments) {
    for (const w of words) {
      if (w.start >= s.from - 0.02 && w.start < s.to) {
        out.push({
          text: w.text,
          start: offset + Math.max(w.start, s.from) - s.from,
          end: offset + Math.min(w.end, s.to) - s.from,
        });
      }
    }
    offset += s.to - s.from;
  }
  return out;
};

type Page = { words: Word[]; start: number; end: number };

// Group words into short caption pages: max N words, break on pauses and punctuation
const toPages = (words: Word[], max: number): Page[] => {
  const pages: Page[] = [];
  let cur: Word[] = [];
  words.forEach((w, i) => {
    const prev = words[i - 1];
    const brk =
      cur.length >= max || (prev && (w.start - prev.end > 0.3 || /[.?!,]$/.test(prev.text)));
    if (cur.length && brk) {
      pages.push({ words: cur, start: cur[0].start, end: w.start });
      cur = [];
    }
    cur.push(w);
  });
  if (cur.length) pages.push({ words: cur, start: cur[0].start, end: cur[cur.length - 1].end + 0.4 });
  return pages;
};

const Captions: React.FC<{ words: Word[] }> = ({ words }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const t = frame / fps;
  const pages = useMemo(() => toPages(words, brand.wordsPerPage), [words]);
  const page = pages.find((p) => t >= p.start && t < p.end);
  if (!page) return null;
  const pop = spring({
    frame: frame - Math.round(page.start * fps),
    fps,
    durationInFrames: 6,
    config: { damping: 200 },
  });
  let active = 0;
  page.words.forEach((w, i) => {
    if (w.start <= t) active = i;
  });
  return (
    <AbsoluteFill style={{ alignItems: "center" }}>
      <div
        style={{
          position: "absolute",
          top: brand.captionTop,
          width: 900,
          textAlign: "center",
          transform: `scale(${0.9 + 0.1 * pop})`,
          fontFamily: brand.captionFont,
          fontSize: brand.captionSize,
          lineHeight: 1.05,
          color: brand.captionColour,
          textTransform: brand.uppercase ? "uppercase" : "none",
          WebkitTextStroke: `${brand.captionStroke}px ${brand.ink}`,
          paintOrder: "stroke fill",
          textShadow: "0 6px 18px rgba(0,0,0,0.45)",
        }}
      >
        {page.words.map((w, i) => (
          <span key={i} style={{ color: i === active ? brand.accent : undefined }}>
            {w.text}
            {i < page.words.length - 1 ? " " : ""}
          </span>
        ))}
      </div>
    </AbsoluteFill>
  );
};

const Hook: React.FC<{ text: string }> = ({ text }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const endF = Math.round(brand.hookSeconds * fps);
  const opacity = interpolate(frame, [0, 4, endF, endF + 8], [0, 1, 1, 0], clamp);
  return (
    <AbsoluteFill style={{ alignItems: "center" }}>
      <div
        style={{
          position: "absolute",
          top: brand.hookTop,
          maxWidth: 940,
          padding: "18px 28px",
          borderRadius: 14,
          background: brand.hookBg,
          color: brand.hookColour,
          fontFamily: brand.hookFont,
          fontSize: brand.hookSize,
          lineHeight: 1.05,
          textAlign: "center",
          textTransform: "uppercase",
          opacity,
        }}
      >
        {text}
      </div>
    </AbsoluteFill>
  );
};

const BrollView: React.FC<{ src: string; durationInFrames: number }> = ({ src, durationInFrames }) => {
  const frame = useCurrentFrame();
  const zoom = interpolate(frame, [0, durationInFrames], [1, 1.06], clamp);
  const style = { width: "100%", height: "100%", objectFit: "cover" } as const;
  return (
    <AbsoluteFill style={{ transform: `scale(${zoom})` }}>
      {/\.(jpe?g|png|webp)$/i.test(src) ? (
        <Img src={staticFile(src)} style={style} />
      ) : (
        <OffthreadVideo src={staticFile(src)} muted style={style} />
      )}
    </AbsoluteFill>
  );
};

const EndCard: React.FC<{ text: string }> = ({ text }) => {
  const frame = useCurrentFrame();
  const opacity = interpolate(frame, [0, 8], [0, 1], clamp);
  return (
    <AbsoluteFill
      style={{ background: brand.endBg, justifyContent: "center", alignItems: "center", gap: 40, opacity }}
    >
      {brand.logo ? <Img src={staticFile(brand.logo)} style={{ width: 420 }} /> : null}
      <div
        style={{
          fontFamily: brand.bodyFont,
          fontWeight: 800,
          fontSize: 64,
          color: brand.ink,
          textAlign: "center",
          maxWidth: 860,
        }}
      >
        {text}
      </div>
    </AbsoluteFill>
  );
};

export const TalkingShort: React.FC<TalkingShortProps> = (p) => {
  const { fps, durationInFrames } = useVideoConfig();
  const f = (s: number) => Math.round(s * fps);
  const words = useMemo(() => mapWords(p.words, p.segments), [p.words, p.segments]);
  const speechEnd = speechSeconds(p.segments);

  let offset = 0;
  const clips = p.segments.map((s, i) => {
    const from = f(offset);
    offset += s.to - s.from;
    const dur = f(offset) - from;
    if (dur <= 0) return null;
    return (
      <Sequence key={i} from={from} durationInFrames={dur}>
        <OffthreadVideo
          src={staticFile(p.src)}
          trimBefore={f(s.from)}
          style={{ width: "100%", height: "100%", objectFit: "cover" }}
        />
      </Sequence>
    );
  });

  return (
    <AbsoluteFill style={{ backgroundColor: "black" }}>
      {clips}
      {(p.broll ?? []).map((b, i) => (
        <Sequence key={`b${i}`} from={f(b.at)} durationInFrames={f(b.dur)}>
          <BrollView src={b.src} durationInFrames={f(b.dur)} />
        </Sequence>
      ))}
      <Sequence durationInFrames={f(speechEnd)}>
        <Captions words={words} />
      </Sequence>
      {p.hook ? (
        <Sequence durationInFrames={f(brand.hookSeconds) + 10}>
          <Hook text={p.hook} />
        </Sequence>
      ) : null}
      {p.endCard ? (
        <Sequence from={f(speechEnd)} durationInFrames={f(END_CARD_SECONDS)}>
          <EndCard text={p.endCardText ?? brand.endCardText} />
        </Sequence>
      ) : null}
      {p.music ? (
        <Audio
          src={staticFile(p.music)}
          loop
          volume={(fr) =>
            (p.musicVolume ?? 0.08) *
            interpolate(fr, [durationInFrames - 20, durationInFrames], [1, 0], clamp)
          }
        />
      ) : null}
    </AbsoluteFill>
  );
};
```

### `src/Root.tsx`

```tsx
import { Composition } from "remotion";
import "./brand";
import { TalkingShort, TalkingShortProps, totalSeconds } from "./TalkingShort/TalkingShort";

const example: TalkingShortProps = {
  src: "work/example/01/source.mp4",
  segments: [{ from: 0, to: 5 }],
  words: [],
  hook: "Example hook",
  endCard: true,
};

export const RemotionRoot: React.FC = () => (
  <Composition
    id="TalkingShort"
    component={TalkingShort}
    fps={30}
    width={1080}
    height={1920}
    durationInFrames={300}
    defaultProps={example}
    calculateMetadata={({ props }) => ({
      durationInFrames: Math.ceil(totalSeconds(props) * 30),
    })}
  />
);
```

For the toolchain test, make `public/work/example/01/source.mp4` from any 5s of any clip with
`make_source.sh`.

## Appendix B: tools

### `tools/transcribe.py`

```python
"""Transcribe with word timings.
Usage: python3 transcribe.py AUDIO OUT.json [model]   (default small.en; try medium.en if poor)
Also writes OUT.txt: one line per sentence with [mm:ss] for reading and shortlisting."""
import json, sys
from faster_whisper import WhisperModel

src, out = sys.argv[1], sys.argv[2]
size = sys.argv[3] if len(sys.argv) > 3 else "small.en"
model = WhisperModel(size, device="cpu", compute_type="int8")
segs, _ = model.transcribe(
    src, language="en", word_timestamps=True, vad_filter=True,
    initial_prompt=("UK property investment. Buy-to-let, BTL, HMO, BRR, serviced accommodation, "
                    "SA, SDLT, stamp duty, remortgage, LTV, bridging, Article 4, consent to let, West Flows."),
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
```

### `tools/face_x.py`

```python
"""Find the horizontal crop for a 9:16 slice that keeps the speaker's face centred.
Usage: python3 face_x.py VIDEO FROM TO
Samples 2 frames per second, prints face centre per sample, the suggested crop X (source pixels)
and MOVES if the face travels too far for one fixed crop."""
import json, subprocess, sys
import cv2, numpy as np

src, start, end = sys.argv[1], float(sys.argv[2]), float(sys.argv[3])
info = json.loads(subprocess.run(
    ["ffprobe", "-v", "error", "-select_streams", "v:0", "-show_entries", "stream=width,height",
     "-of", "json", src], capture_output=True, check=True).stdout)["streams"][0]
W, H = info["width"], info["height"]
sw, sh = 960, int(round(960 * H / W / 2) * 2)
raw = subprocess.run(
    ["ffmpeg", "-v", "error", "-ss", str(start), "-t", str(end - start), "-i", src,
     "-vf", f"fps=2,scale={sw}:{sh}", "-f", "rawvideo", "-pix_fmt", "gray", "-"],
    capture_output=True, check=True).stdout
frames = np.frombuffer(raw, np.uint8).reshape(-1, sh, sw)
casc = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
centres = []
for i, g in enumerate(frames):
    faces = casc.detectMultiScale(g, 1.1, 5, minSize=(40, 40))
    if len(faces):
        x, y, w, h = max(faces, key=lambda r: r[2] * r[3])
        cx = (x + w / 2) * W / sw
        centres.append(cx)
        print(f"{start + i / 2:7.1f}s  face x={cx:.0f}")
    else:
        print(f"{start + i / 2:7.1f}s  no face")
crop_w = int(H * 9 / 32) * 2
if not centres:
    print(f"No face found. Centre crop X = {(W - crop_w) // 2}"); sys.exit()
med = float(np.median(centres))
x = int(min(max(med - crop_w / 2, 0), W - crop_w)) // 2 * 2
spread = float(np.percentile(centres, 90) - np.percentile(centres, 10))
print(f"crop width {crop_w}, suggested CROPX {x}, face spread {spread:.0f}px")
if spread > crop_w * 0.3:
    print("MOVES: split this moment at a sentence boundary and crop each part separately")
```

### `tools/make_source.sh`

```bash
#!/usr/bin/env bash
# Cut a moment from the long video, crop to 9:16 at CROPX, scale to 1080x1920, clean the voice.
# Usage: make_source.sh VIDEO FROM TO CROPX OUT.mp4
set -euo pipefail
GRADE="${GRADE:-null}"   # e.g. GRADE="eq=gamma=1.03:saturation=1.05" after testing against references
mkdir -p "$(dirname "$5")"
dur=$(python3 -c "print(round($3 - $2, 3))")
ffmpeg -v error -y -ss "$2" -t "$dur" -i "$1" \
  -vf "crop=trunc(ih*9/32)*2:ih:$4:0,scale=1080:1920:flags=lanczos,$GRADE" \
  -af "highpass=f=80,acompressor=threshold=-20dB:ratio=3:attack=5:release=120,loudnorm=I=-14:TP=-1.5:LRA=11" \
  -r 30 -c:v libx264 -crf 18 -preset medium -g 15 -pix_fmt yuv420p \
  -c:a aac -b:a 192k -ar 48000 "$5"
echo "made $5 ($dur s)"
```

### `tools/plan_cuts.py`

```python
"""Turn a moment into Remotion props: keep speech, drop long pauses and filler words.
Usage: python3 plan_cuts.py TRANSCRIPT.json FROM TO SRC_IN_PUBLIC OUT_PROPS.json [--no-trim]
Times in the output are relative to source.mp4 (which starts at FROM)."""
import json, sys

tr_path, start, end, src, out = sys.argv[1], float(sys.argv[2]), float(sys.argv[3]), sys.argv[4], sys.argv[5]
no_trim = "--no-trim" in sys.argv
GAP, PAD = 0.35, 0.08
FILLERS = {"um", "uh", "erm", "er", "ah", "hmm", "mm"}

words = [w for w in json.load(open(tr_path))["words"] if w["start"] >= start and w["end"] <= end]
keep = [w for w in words if w["text"].lower().strip(".,!?") not in FILLERS]

if no_trim or not keep:
    segs = [[0.0, end - start]]
else:
    segs = []
    for w in keep:
        s, e = max(w["start"] - PAD, start), min(w["end"] + PAD, end)
        if segs and s - segs[-1][1] <= GAP:
            segs[-1][1] = max(segs[-1][1], e)
        else:
            segs.append([s, e])
    segs = [[s - start, e - start] for s, e in segs]

props = {
    "src": src,
    "segments": [{"from": round(s, 3), "to": round(e, 3)} for s, e in segs],
    "words": [{"text": w["text"], "start": round(w["start"] - start, 3), "end": round(w["end"] - start, 3)}
              for w in keep],
    "hook": "TODO",
    "broll": [],
    "music": None,
    "musicVolume": 0.08,
    "endCard": True,
    "endCardText": "Free deal calculator: link in bio",
}
json.dump(props, open(out, "w"), indent=1)
kept = sum(s["to"] - s["from"] for s in props["segments"])
print(f"{len(segs)} segments, {kept:.1f}s kept of {end - start:.1f}s")
```

### `tools/contact_sheet.sh`

```bash
#!/usr/bin/env bash
# Timestamped frames tiled into one image so Claude can "watch" a clip.
# Usage: contact_sheet.sh INPUT OUTPUT.jpg [SECONDS_PER_FRAME]
set -euo pipefail
step=${3:-1}
font=$(fc-list : file | grep -i 'DejaVuSans-Bold.ttf' | head -1 | cut -d: -f1)
dur=$(ffprobe -v error -show_entries format=duration -of csv=p=0 "$1")
rows=$(python3 -c "import math;print(max(1,math.ceil($dur/$step/10)))")
ffmpeg -v error -y -i "$1" -vf "fps=1/$step,scale=180:-2,drawtext=fontfile=$font:text='%{eif\:t\:d}s':x=4:y=4:fontsize=18:fontcolor=white:box=1:boxcolor=black@0.6,tile=10x$rows:padding=3:color=white" -frames:v 1 "$2"
```
For long videos at 5s steps the sheet gets tall; if it is hard to read, split by time range
(`-ss`/`-t`) into 5-minute sheets.

## Appendix C: `library/<slug>/moments.json`

```json
{
  "slug": "btl-mortgages",
  "video": "input/btl-mortgages/video.mp4",
  "topic": "Residential vs buy-to-let mortgages",
  "moments": [
    {
      "id": "01",
      "from": 312.4,
      "to": 351.0,
      "hook_line": "Most first-time landlords get this completely wrong",
      "hook_title": "Your mortgage could be illegal",
      "idea": "Letting on a residential mortgage without consent to let",
      "payoff": "Ask the lender for consent to let before the tenant moves in",
      "cta": "calculator",
      "score": 8,
      "cropX": 1300,
      "status": "shortlisted"
    }
  ]
}
```
`status`: shortlisted → picked → rendered → approved → posted.

## Appendix D: kick-off prompts

New project, first time:
> Read HANDOVER.md and set up the project (Section 3). Then analyse everything in reference/
> and write library/STYLE.md (Section 4). Show me STYLE.md before doing anything else.

Each new long video:
> New video in input/<slug>/. Run Section 5 up to the shortlist and show me the table.

After I pick:
> Make 1, 3 and 5. Show me the first one before doing the rest.

---

## Appendix E: changes since this handover was written (live code wins over Appendices A-B)

- Project lives in `westflows-shorts/` inside the `my-wordpress-site` repo. Remotion 4.0.535.
- `.gitignore` paths are anchored (`/reference/`, `/input/` ...) so `library/reference/` stays tracked.
- Cloud browser: `--browser-executable /opt/pw-browsers/chromium_headless_shell-1194/chrome-linux/headless_shell`
- Inter is the variable font saved as `public/brand/fonts/Inter.ttf`, loaded as weight `100 900`.
- `transcribe.py` prompt contains fillers so Whisper writes down "um / uh / erm" instead of skipping them.
- `plan_cuts.py` really cuts (audio and video, not just captions): fillers, false starts (`I-`),
  stutters / repeated words ("I I", "it was it was"; keeps the second), and bad takes via
  `--drop=A-B,C-D` (long-video seconds). `--cta=calculator|landlord` sets the end card.
  It prints every cut: check it, because real repeats ("very very", "no no") are cut too.
- Shortlist priority: the inspirational and business parts (houses, deals, mindset, numbers).
- Motion graphics: `props.json` takes `graphics`, times in OUTPUT seconds (after cuts):
  ```json
  {"type":"stat","at":2.4,"dur":1.6,"value":1250,"prefix":"£","suffix":"","decimals":0,"label":"rent a month"}
  {"type":"list","at":4.3,"dur":5.2,"title":"Check first","items":[{"text":"Yield","at":5.8}]}
  {"type":"label","at":8.4,"dur":1.6,"text":"Article 4"}
  {"type":"zoom","at":1.3,"dur":0.9,"scale":1.2}
  ```
  Stat and list cards sit at `brand.graphicTop`, labels at `brand.labelTop`. Zoom is capped at 1.3.
- Theme: orange `#FF8D48` accent on captions, hook, stats, ticks. Keep it.
