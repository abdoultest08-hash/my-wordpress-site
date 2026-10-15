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
