# Video editing with Claude + Remotion: handover

Everything we built for the Céleste fashion videos, written so it can be reused in a new
project (a YouTube channel on property and business). Give this file to Claude in the new
project and say: "read HANDOVER.md, set up the same workflow".

---

## 1. What we built

- A **Remotion** project (video made with React code) that turns raw footage into short
  vertical edits (1080x1920, 30fps, 12-18s) for TikTok / Reels.
- One reusable template, `Lookbook`: a list of shots + a list of cut times + a song. Each shot
  is a video clip (with a start time) or a still photo. Every shot gets a slow push-in; any shot
  can cross-dissolve in; the video can fade out to a colour.
- Cuts placed **on the beat**: measured from the song with `tools/beat.py`.
- A **colour grade** applied to all footage with ffmpeg before editing (bright, clean whites,
  rich blacks).
- **Close-ups** made by cropping 2x into 4K footage or high-resolution photos (stays sharp at 1080p).
- A **media library**: `library/catalog.json` + contact sheets, listing every clip's product,
  model, set, vibe, good moments and parts to avoid (in seconds). Edits are planned from it.

## 2. The workflow, step by step

1. **Footage in**: user uploads to a Dropbox shared folder, organised by subject
   (`Raw/<Topic>/`, `Reference/`, `Music/`, `Brand/`), and sends the shared link.
2. **Download**: `curl -L -o all.zip "<shared link with dl=1>"`, then `unzip`.
   Keep media in the session scratchpad, never in Git.
3. **Look at everything**: `tools/contact_sheet.sh clip.mp4 sheet.jpg` gives one timestamped
   frame per second in a single image. Claude reads the images to judge content, framing and
   problems (empty frames, gear in shot, camera spins, out of frame).
4. **Catalogue**: record good moments / avoid ranges per clip in `library/catalog.json`.
5. **Analyse references**: cut times via ffmpeg scene detection
   (`ffmpeg -i ref.mp4 -vf "select='gt(scene,0.25)',showinfo" -an -f null - 2>&1 | grep pts_time`),
   style notes from contact sheets.
6. **Music**: `python3 tools/beat.py song.mp3 [start end]` gives the beat length, BPM and
   loudness per beat (a jump = the drop). Cut the song section with ffmpeg and normalise to
   social loudness: `-af loudnorm=I=-14:TP=-1.5:LRA=11`.
7. **Prepare footage**: 1080p graded copies of the clips used (fast to edit):
   `ffmpeg -i in.mp4 -vf "scale=1080:1920,<GRADE>" -an -c:v libx264 -crf 18 -g 15 -pix_fmt yuv420p out.mp4`
   Close-ups: crop 1080x1920 straight from the 4K original (`crop=1080:1920:X:Y,<GRADE>`).
   Photos: crop a 9:16 region (zoom up to ~2x on 2700x4050 photos), scale to 1080x1920, same grade.
8. **Plan the edit**: shot list = hook first (strongest image in the first second), alternate
   wide / detail, one cut per beat or per 2 beats, end on a strong pose + fade.
9. **Render**: `npx remotion render <CompositionId> out/name.mp4 --codec h264 --crf 18 --browser-executable <chromium>`
10. **Check before sending**: contact sheet of the render (4 fps), `blackdetect` for black frames,
    `ebur128` for loudness (target about -14 LUFS), exact duration. Then send the MP4.
11. **Commit** code + catalogue (not media) and push.

## 3. Environment gotchas (Claude Code cloud sessions)

- **Network**: the environment must allow the file host. We had to add Dropbox / Google Drive
  in the environment settings (Network access: broader level or allowed domains
  `www.dropbox.com`, `dl.dropboxusercontent.com`, `*.dropboxusercontent.com`,
  `drive.google.com`, `drive.usercontent.google.com`), keeping "Allow package managers" ticked.
- **Remotion's own Chrome download is blocked** (remotion.media). Use the pre-installed one:
  `--browser-executable /opt/pw-browsers/chromium_headless_shell-1194/chrome-linux/headless_shell`
  (check the folder name with `ls /opt/pw-browsers`).
- **Dropbox**: a shared-folder link with `dl=1` downloads the whole folder as one zip; a
  subfolder cannot be downloaded alone through the link. Very large folders may fail to zip:
  share separate links per subfolder or per file. Filenames with spaces: always quote paths.
- **Disk**: about 30 GB per session. Delete zips after extracting. The session is temporary:
  media must be re-downloaded in a new session; code and the catalogue live in Git.
- **Claude cannot hear audio.** Beats come from `beat.py`; speech must be transcribed (see 6).
  Always ask the user to check the feel of the music.
- Scaffold a new project with `npx create-video@latest --yes --blank <dir>` (non-interactive).

## 4. Colour grade (ffmpeg filter)

```
eq=gamma=1.08:contrast=1.0:saturation=1.07,colorbalance=rh=-0.015:bh=0.02:rm=0.015:bm=-0.005,curves=all='0/0 0.15/0.14 0.5/0.56 0.8/0.88 1/1'
```
Brighter mids and highlights, slightly cooler whites, blacks protected (a plain gamma lift
turned black fabric grey). Test on a few frames side by side before applying to everything.

## 5. Lessons learned

- Reuse a reference edit's **cut times**: they already sit on its song's beats.
- Detect the beat from the audio; a tempo under ~70 BPM is often half-time (cut every beat
  or every half beat).
- Start the song section on a beat, ideally on the drop, so the first cut hits.
- 4K source lets you make sharp 2x close-ups for 1080p output; for 4K output, zoom less.
- Social apps re-compress to ~1080p: deliver 1080x1920; make a 4K master only for web/YouTube.
- Music rights: commercial tracks (screen-recorded) get muted or blocked for ads. Post silent
  and add the sound inside TikTok/Instagram, or use royalty-free libraries (YouTube Audio
  Library, Pixabay Music, Uppbeat, Epidemic Sound).
- Check every render frame by frame: we caught a light stand, a studio pole and the model
  walking out of frame this way.

## 6. Plan for the YouTube channel (long video to shorts)

Source: 20-min 4K (3840x2160, about 7 GB) talking videos on property / business.

1. Download the original file (not a compressed copy). Work in the scratchpad; delete after.
2. **Transcribe with timestamps**: `pip install faster-whisper` and run a small/medium model on
   CPU (a few minutes for 20 min). Save the transcript (JSON with word timings) to Git.
3. **Visual scan**: `contact_sheet.sh video.mp4 sheet.jpg 5` (one frame per 5s).
4. **Shortlist 5-10 moments** of 20-45s: a strong first sentence (hook), one clear idea,
   a payoff. Present them as timestamp + quote; the user picks.
5. **Build the short** in Remotion:
   - Vertical crop from 16:9 4K: a 1215x2160 slice scaled to 1080x1920 (sharp). Follow the
     speaker with keyframed crop positions, or detect the face (OpenCV) to set them.
   - Burned-in subtitles from the word timings (`@remotion/captions`), 2-4 words at a time,
     large and bold, highlighted word, kept clear of the TikTok/Reels interface zones.
   - Hook title on screen for the first 1-2s; optional B-roll / stills (property photos,
     charts) cut in on key words; light music bed under speech (about -20 dB under voice).
   - Channel branding: colours, font, logo end card; loudness about -14 LUFS.
6. Same checks as section 2, step 10, then send and commit.

---

## Appendix A: `src/Lookbook/Lookbook.tsx` (the edit template)

```tsx
import {
  AbsoluteFill,
  Audio,
  Img,
  interpolate,
  OffthreadVideo,
  Sequence,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";

export type Shot = {
  // File inside public/: a video ("graded/clip2.mp4") or a still photo ("photos/cj-tag.jpg")
  src: string;
  // Where to start inside the source clip, in seconds (ignored for photos)
  from: number;
  // Soft cross-dissolve from the previous shot instead of a hard cut
  fade?: boolean;
};

export type LookbookProps = {
  shots: Shot[];
  // Cut times in seconds; cuts[i] is where shots[i] starts, last entry is the end
  cuts: number[];
  audio: string;
  // Fade the picture out to this colour over the last frames
  fadeOutTo?: string;
};

// Length of a cross-dissolve, centred on the cut
const FADE_FRAMES = 8;
const FADE_OUT_FRAMES = 15;

const ShotView: React.FC<{ shot: Shot; index: number; durationInFrames: number }> = ({
  shot,
  index,
  durationInFrames,
}) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  // Slow push-in, alternating in/out so consecutive shots feel different
  const zoom = interpolate(frame, [0, durationInFrames], index % 2 === 0 ? [1, 1.06] : [1.06, 1], {
    extrapolateRight: "clamp",
  });
  const opacity = shot.fade
    ? interpolate(frame, [0, FADE_FRAMES], [0, 1], { extrapolateRight: "clamp" })
    : 1;

  const style = { width: "100%", height: "100%", objectFit: "cover" } as const;

  return (
    <AbsoluteFill style={{ transform: `scale(${zoom})`, opacity }}>
      {/\.(jpe?g|png)$/i.test(shot.src) ? (
        <Img src={staticFile(shot.src)} style={style} />
      ) : (
        <OffthreadVideo
          src={staticFile(shot.src)}
          trimBefore={Math.max(0, Math.round(shot.from * fps) - (shot.fade ? FADE_FRAMES / 2 : 0))}
          muted
          style={style}
        />
      )}
    </AbsoluteFill>
  );
};

export const Lookbook: React.FC<LookbookProps> = ({ shots, cuts, audio, fadeOutTo }) => {
  const frame = useCurrentFrame();
  const { fps, durationInFrames } = useVideoConfig();
  const toFrame = (s: number) => Math.round(s * fps);

  return (
    <AbsoluteFill style={{ backgroundColor: "black" }}>
      {shots.map((shot, i) => {
        // A fading shot starts half a dissolve early; every shot runs a little
        // past its cut so there is always a picture underneath the next fade.
        const start = toFrame(cuts[i]) - (shot.fade ? FADE_FRAMES / 2 : 0);
        const end = toFrame(cuts[i + 1]) + (i < shots.length - 1 ? FADE_FRAMES : 0);
        return (
          <Sequence key={i} from={start} durationInFrames={end - start}>
            <ShotView shot={shot} index={i} durationInFrames={end - start} />
          </Sequence>
        );
      })}
      {fadeOutTo ? (
        <AbsoluteFill
          style={{
            backgroundColor: fadeOutTo,
            opacity: interpolate(
              frame,
              [durationInFrames - FADE_OUT_FRAMES, durationInFrames - 1],
              [0, 1],
              { extrapolateLeft: "clamp", extrapolateRight: "clamp" },
            ),
          }}
        />
      ) : null}
      <Audio
        src={staticFile(audio)}
        volume={(f) =>
          interpolate(f, [durationInFrames - 10, durationInFrames], [1, 0], {
            extrapolateLeft: "clamp",
            extrapolateRight: "clamp",
          })
        }
      />
    </AbsoluteFill>
  );
};
```

Example edit (cut times from beats, video + photo shots):

```ts
const onBeats = (beatSeconds: number, beats: number[]) =>
  beats.map((b) => Math.round(b * beatSeconds * 1000) / 1000);

export const studioRoseNere: LookbookProps = {
  audio: "audio/rose-nere-drop.m4a", // song section starting on the drop, loudness-normalised
  fadeOutTo: "white",
  cuts: onBeats(0.5341, [0, 2, 4, 5, 6, 8, 10, 11, 12, 14, 16, 17, 18, 20, 22, 23, 26]),
  shots: [
    { src: "graded/clip28.mp4", from: 8.0 }, // hook
    { src: "graded/CJ-65.mp4", from: 3.5 },
    { src: "photos/brf-chestlogo.jpg", from: 0 }, // photo flash, one beat
    // ...one shot per cut interval; add fade: true for a cross-dissolve
  ],
};
```

Register it in `src/Root.tsx`:

```tsx
<Composition id="StudioRoseNere" component={Lookbook}
  durationInFrames={Math.round(26 * 0.5341 * 30)} fps={30} width={1080} height={1920}
  defaultProps={studioRoseNere} />
```

## Appendix B: `tools/beat.py`

```python
"""Estimate a song's tempo and beat times (no extra libraries beyond numpy).

Usage: python3 beat.py SONG [START_SECONDS END_SECONDS]
Prints the beat length, BPM, beat times and loudness per beat (a big jump = the drop).
"""
import subprocess, sys
import numpy as np

path = sys.argv[1]
start = float(sys.argv[2]) if len(sys.argv) > 2 else 0.0
end = float(sys.argv[3]) if len(sys.argv) > 3 else None
sr, hop, win = 22050, 256, 1024

cmd = ["ffmpeg", "-v", "error", "-ss", str(start), "-i", path]
if end:
    cmd += ["-t", str(end - start)]
cmd += ["-vn", "-ac", "1", "-ar", str(sr), "-f", "s16le", "-"]
x = np.frombuffer(subprocess.run(cmd, capture_output=True, check=True).stdout, np.int16) / 32768.0

frames = np.lib.stride_tricks.sliding_window_view(x, win)[::hop] * np.hanning(win)
mag = np.abs(np.fft.rfft(frames, axis=1))
flux = np.maximum(0, np.diff(np.log1p(mag), axis=0)).sum(1)
flux = flux - flux.mean()
fps = sr / hop

ac = np.correlate(flux, flux, "full")[len(flux) - 1 :]
lags = np.arange(len(ac)) / fps
m = (lags > 0.3) & (lags < 1.2)
period = lags[m][np.argmax(ac[m])]

phases = np.linspace(0, period, 200)
score = [np.interp(np.arange(p, len(flux) / fps, period) * fps, np.arange(len(flux)), flux).sum() for p in phases]
beats = start + np.arange(phases[int(np.argmax(score))], len(flux) / fps, period)

print(f"beat every {period:.4f}s = {60 / period:.1f} BPM (a value under ~70 may be half-time)")
print("beats:", " ".join(f"{b:.3f}" for b in beats[:60]))
db = [20 * np.log10(np.sqrt((x[int((b - start) * sr) : int((b - start + period) * sr)] ** 2).mean()) + 1e-9) for b in beats[:60]]
print("dB per beat:", " ".join(f"{d:.0f}" for d in db))
```

## Appendix C: `tools/contact_sheet.sh`

```bash
#!/usr/bin/env bash
# One frame per second, timestamped, tiled: lets Claude "watch" a clip as a single image.
# Usage: contact_sheet.sh INPUT OUTPUT.jpg [SECONDS_PER_FRAME]
set -euo pipefail
step=${3:-1}
font=$(fc-list : file | grep -i 'DejaVuSans-Bold.ttf' | head -1 | cut -d: -f1)
dur=$(ffprobe -v error -show_entries format=duration -of csv=p=0 "$1")
rows=$(python3 -c "import math;print(max(1,math.ceil($dur/$step/10)))")
ffmpeg -v error -y -i "$1" -vf "fps=1/$step,scale=180:-2,drawtext=fontfile=$font:text='%{eif\:t\:d}s':x=4:y=4:fontsize=18:fontcolor=white:box=1:boxcolor=black@0.6,tile=10x$rows:padding=3:color=white" -frames:v 1 "$2"
```

## Appendix D: library catalogue entry (shape)

```json
{ "id": "BRF-14", "path": "Raw/Tracksuit-Brown-Female/14.mp4", "product": "tracksuit-brown",
  "model": "F1", "set": "sofa-curtain", "duration": 13.5, "vibe": ["soft", "beauty"],
  "good":  [ { "from": 3, "to": 10, "shot": "slow push-in to face + chest logo" } ],
  "avoid": [ { "from": 11, "to": 13.5, "why": "drifts out of frame" } ] }
```
For the YouTube project, use the same idea per long video: topic, speaker, location, and
a list of moments with from/to, the quote and why it works as a short.
