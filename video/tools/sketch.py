"""Turn a studio photo into a pencil line sketch (transparent PNG).
usage: sketch.py PHOTO OUT_PNG"""
import sys
import numpy as np
from PIL import Image, ImageFilter
from scipy import ndimage as ndi
src, out = sys.argv[1:3]
im = Image.open(src).convert("RGB"); im = im.resize((im.width // 2, im.height // 2), Image.LANCZOS)
a = np.array(im).astype(np.float32)
g = a.mean(2)
# subject mask: differs from the pale backdrop
bg = np.median(np.r_[a[:40].reshape(-1, 3), a[:, :40].reshape(-1, 3), a[:, -40:].reshape(-1, 3)], 0)
diff = np.sqrt(((a - bg) ** 2).sum(2))
subj = ndi.binary_fill_holes(ndi.binary_opening(diff > 38, iterations=3))
lab, n = ndi.label(subj); sizes = ndi.sum(subj, lab, range(1, n + 1)); subj = lab == (np.argmax(sizes) + 1)
# outline + inner seams/folds from smoothed gradients
gs = ndi.gaussian_filter(g, 2.2)
mag = np.hypot(ndi.sobel(gs, 0), ndi.sobel(gs, 1))
inner = (mag > np.percentile(mag[subj], 88)) & ndi.binary_erosion(subj, iterations=4)
outline = subj ^ ndi.binary_erosion(subj, iterations=2)
lines = ndi.binary_opening(inner, iterations=1) | outline
# thin-ish, soft pencil look
ink = ndi.gaussian_filter(lines.astype(np.float32), 0.9)
ink = np.clip(ink * 1.6, 0, 1) * 0.88
rgba = np.zeros(g.shape + (4,), np.uint8); rgba[..., :3] = (38, 34, 31); rgba[..., 3] = (ink * 255).astype(np.uint8)
ys, xs = np.where(subj); pad = 30
Image.fromarray(rgba).crop((max(0, xs.min() - pad), max(0, ys.min() - pad), xs.max() + pad, ys.max() + pad)).save(out)
