"""Print three photos onto the paper strip coming out of the copier.
usage: printpaper.py PRINTER_PNG OUT_JPG PHOTO1 PHOTO2 PHOTO3"""
import sys
import numpy as np
from PIL import Image, ImageFilter
pp, out, *photos = sys.argv[1:]
im = np.array(Image.open(pp).convert("RGB")).astype(np.float32); H, W, _ = im.shape
L = im.mean(2); sat = im.max(2) - im.min(2)
Y0, Y1 = 945, 2490
left = np.full(H, np.nan); right = np.full(H, np.nan); mask = np.zeros((H, W), bool)
for y in range(Y0, Y1):
    m = (L[y] > 170) & (sat[y] < 42); m[:330] = False; m[1460:] = False
    best = (0, 0); s = None
    for x in range(W):
        if m[x] and s is None: s = x
        if (not m[x] or x == W - 1) and s is not None:
            if x - s > best[1] - best[0]: best = (s, x)
            s = None
    if best[1] - best[0] > 60:
        left[y], right[y] = best; mask[y, best[0]:best[1]] = True
ys = np.arange(H)
ok = ~np.isnan(left)
def smooth(a):
    v = np.interp(ys, ys[ok], a[ok]); k = np.ones(121) / 121
    return np.convolve(np.pad(v, 60, mode="edge"), k, mode="valid")
# the far end of the strip is cut on a diagonal: trust edges only where the run is wide
wid = right - left; good = ok & (wid > np.nanmedian(wid[ok]) * 0.8)
ok = good
lft, rgt = smooth(left), smooth(right); w = rgt - lft
# arc length along the strip: hanging part ~ dy/w, part lying on the floor ~ dy*w0/w^2
w0 = np.median(w[1100:1300])
dv = np.where(ys < 1380, 1.35 / w, (w0 / w) ** 0.4 / w)
dv[:Y0] = 0; dv[Y1:] = 0
v = np.cumsum(dv); vtot = v[Y1 - 1]
# texture: three prints with white margins, paper width = TW px
TW = 1000; TL = int(vtot * TW)
tex = Image.new("RGB", (TW, TL), (246, 243, 237))
mar = int(TW * 0.08); slot = (TL - mar * 4) // 3
for i, p in enumerate(photos):
    ph = Image.open(p).convert("RGB")
    bw, bh = TW - 2 * mar, slot
    r = ph.width / ph.height
    if bw / bh > r: nh = bh; nw = int(bh * r)
    else: nw = bw; nh = int(bw / r)
    ph = ph.resize((nw, nh), Image.LANCZOS)
    tex.paste(ph, ((TW - nw) // 2, mar + i * (slot + mar) + (slot - nh) // 2))
tex = np.array(tex.filter(ImageFilter.GaussianBlur(0.6))).astype(np.float32)
# map every paper pixel to the texture
yy, xx = np.mgrid[0:H, 0:W]
u = (xx - lft[:, None]) / w[:, None]; vv = (v[:, None] / vtot) * np.ones((1, W))
tx = np.clip(u * (TW - 1), 0, TW - 1); ty = np.clip(vv * (TL - 1), 0, TL - 1)
x0 = tx.astype(int); y0 = ty.astype(int); x1 = np.minimum(x0 + 1, TW - 1); y1 = np.minimum(y0 + 1, TL - 1)
fx = (tx - x0)[..., None]; fy = (ty - y0)[..., None]
samp = (tex[y0, x0] * (1 - fx) + tex[y0, x1] * fx) * (1 - fy) + (tex[y1, x0] * (1 - fx) + tex[y1, x1] * fx) * fy
# keep the paper's own light and folds: shade the print by the blank paper's brightness
shade = np.clip(L / 236.0, 0.35, 1.08)[..., None]
printed = samp * shade
a = Image.fromarray((mask * 255).astype(np.uint8)).filter(ImageFilter.MinFilter(5)).filter(ImageFilter.GaussianBlur(1.5))
a = (np.array(a).astype(np.float32) / 255)[..., None]
res = im * (1 - a) + printed * a
noise = np.random.default_rng(3).normal(0, 2.2, res.shape)
Image.fromarray(np.clip(res + noise, 0, 255).astype(np.uint8)).save(out, quality=94)
print("strip length in widths:", round(vtot, 2))
