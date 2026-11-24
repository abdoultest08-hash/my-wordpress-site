"""Clean plate: fill the box area with the tread from two steps further up (same phase, perspective
mapped), then match brightness row by row to the clear tread beside the box."""
import sys
import numpy as np
from PIL import Image, ImageDraw, ImageFilter
src_p, out_p = sys.argv[1:3]
im = np.array(Image.open(src_p).convert("RGB")).astype(np.float32); H, W, _ = im.shape
EDGES = np.array([1246, 1268, 1293, 1324, 1361, 1403, 1457, 1516, 1589, 1680, 1803, 1977, 2250], float)
KS = np.arange(len(EDGES), dtype=float)
y_of_k = lambda k: np.interp(k, KS, EDGES)
k_of_y = lambda y: np.interp(y, EDGES, KS)
CX = 770.0
hw = lambda y: np.interp(y, [1136, 1250, 1450, 1650, 1850, 2300, 2500, 2700], [70, 102, 166, 190, 245, 431, 466, 489])
poly = [(576, 1878), (962, 1878), (984, 2138), (974, 2216), (562, 2216), (552, 2138)]
m = Image.new("L", (W, H), 0); ImageDraw.Draw(m).polygon(poly, fill=255)
m = m.filter(ImageFilter.MaxFilter(11))
soft = np.array(m.filter(ImageFilter.GaussianBlur(4))).astype(np.float32) / 255
hard = np.array(m).astype(np.float32) / 255
y0, y1 = 1850, 2240
yy, xx = np.mgrid[y0:y1, 0:W].astype(np.float32)
ys = y_of_k(k_of_y(yy) - 2.0); xs = CX + (xx - CX) * hw(ys) / hw(yy)
def sample(x, y):
    x = np.clip(x, 0, W - 1.001); y = np.clip(y, 0, H - 1.001)
    a = np.floor(x).astype(int); b = np.floor(y).astype(int); fx = (x - a)[..., None]; fy = (y - b)[..., None]
    return (im[b, a] * (1 - fx) + im[b, a + 1] * fx) * (1 - fy) + (im[b + 1, a] * (1 - fx) + im[b + 1, a + 1] * fx) * fy
fill = sample(xs, ys)
# smooth brightness correction from a thin ring of tread around the box (masked blur)
from scipy.ndimage import gaussian_filter, binary_dilation
box = hard[y0:y1] > 0.5
ring = binary_dilation(box, iterations=40) & ~binary_dilation(box, iterations=6)
ring &= (np.abs(xx - CX) < hw(yy) * 0.82)
orig = im[y0:y1]
corr = np.ones_like(fill)
for c in range(3):
    num = gaussian_filter(np.where(ring, orig[..., c], 0), 25); den = gaussian_filter(np.where(ring, fill[..., c], 0), 25)
    wgt = gaussian_filter(ring.astype(np.float32), 25)
    ratio = np.where(wgt > 1e-3, (num + 1) / (den + 1), 1.0)
    corr[..., c] = ratio
out = im.copy()
blend = soft[y0:y1, :, None]
out[y0:y1] = orig * (1 - blend) + fill * corr * blend
Image.fromarray(np.clip(out, 0, 255).astype(np.uint8)).save(out_p)
