"""Realistic Tube escalator, camera at the top looking down: steps glide towards the camera with the
fitted perspective and one HLC box rides up from far away to its spot in the photo.
usage: tube.py STILL OUT_DIR SECONDS FPS CLEAN_PLATE"""
import sys, os
import numpy as np
from PIL import Image, ImageDraw, ImageFilter
still, outdir, secs, fps = sys.argv[1], sys.argv[2], float(sys.argv[3]), int(sys.argv[4])
os.makedirs(outdir, exist_ok=True)
src = np.array(Image.open(still).convert("RGB")).astype(np.float32); SH, SW, _ = src.shape

# measured step edges (k = step index counted towards the camera); piecewise-linear between them,
# extrapolated with the end gaps
EDGES = np.array([1246, 1268, 1293, 1324, 1361, 1403, 1457, 1516, 1589, 1680, 1803, 1977, 2250], float)
KS = np.arange(len(EDGES), dtype=float)
def y_of_k(k):
    k = np.asarray(k, float); y = np.interp(k, KS, EDGES)
    y = np.where(k < 0, EDGES[0] + k * (EDGES[1] - EDGES[0]), y)
    return np.where(k > KS[-1], EDGES[-1] + (k - KS[-1]) * (EDGES[-1] - EDGES[-2]), y)
def k_of_y(y):
    y = np.asarray(y, float); k = np.interp(y, EDGES, KS)
    k = np.where(y < EDGES[0], (y - EDGES[0]) / (EDGES[1] - EDGES[0]), k)
    return np.where(y > EDGES[-1], KS[-1] + (y - EDGES[-1]) / (EDGES[-1] - EDGES[-2]), k)
CX = 770.0
hw = lambda y: np.interp(y, [1136, 1250, 1450, 1650, 1850, 2300, 2500, 2700], [70, 102, 166, 190, 245, 431, 466, 489])
Y0, Y1 = 1246.0, 2236.0               # animated part of the incline (far landing .. just past the box)

# output = 9:16 crop of the still, bottom just under the box (keeps the flat landing out of frame)
CB = 2262; CH = CB; CW = round(CH * 9 / 16); CXL = int(CX - CW / 2)
OW, OH = 1080, 1920; s = OW / CW

def sample(img, x, y):
    x = np.clip(x, 0, SW - 1.001); y = np.clip(y, 0, SH - 1.001)
    x0 = np.floor(x).astype(int); y0 = np.floor(y).astype(int); fx = (x - x0)[..., None]; fy = (y - y0)[..., None]
    return (img[y0, x0] * (1 - fx) + img[y0, x0 + 1] * fx) * (1 - fy) + (img[y0 + 1, x0] * (1 - fx) + img[y0 + 1, x0 + 1] * fx) * fy

# --- clean plate: fill the box (and its contact shadow) with the step pattern from two steps further up
poly = [(576, 1878), (962, 1878), (984, 2138), (974, 2222), (562, 2222), (552, 2138)]
mask = Image.new("L", (SW, SH), 0); ImageDraw.Draw(mask).polygon(poly, fill=255)
mask_np = np.array(mask.filter(ImageFilter.MaxFilter(15)).filter(ImageFilter.GaussianBlur(5))).astype(np.float32) / 255
yy, xx = np.mgrid[0:SH, 0:SW].astype(np.float32)
plate = np.array(Image.open(sys.argv[5]).convert("RGB")).astype(np.float32)   # AI clean plate (box removed)

# --- box sprite (from the original photo) with its alpha
bx0, by0, bx1, by1 = 540, 1870, 1000, 2226
box_alpha = Image.new("L", (SW, SH), 0); ImageDraw.Draw(box_alpha).polygon(poly, fill=255)
box_alpha = box_alpha.filter(ImageFilter.GaussianBlur(1.2)).crop((bx0, by0, bx1, by1))
box_rgb = Image.fromarray(src.astype(np.uint8)).crop((bx0, by0, bx1, by1))
box = box_rgb.convert("RGBA"); box.putalpha(box_alpha)
BOX_BOTTOM_Y = 2212.0; K_BOX = k_of_y(BOX_BOTTOM_Y); BOX_CX = (bx0 + bx1) / 2

# --- output pixel grid in source coordinates
oy, ox = np.mgrid[0:OH, 0:OW].astype(np.float32)
sy = oy / s; sx = CXL + ox / s
kk = k_of_y(sy)
inband = (sy > Y0) & (sy < Y1) & (np.abs(sx - CX) < hw(sy) - 3)
static = sample(plate, sx, sy)
SPEED = 0.72                                    # steps per second, calm
n = int(round(secs * fps))
for f in range(n):
    t = f / fps
    ph = (SPEED * t) % 1.0
    # Seamless loop: blend the pattern sampled at k - ph with the one a step nearer (k - ph + 1).
    # At ph = 0 that is S(k); as ph -> 1 the weight moves fully to S(k - 1 + 1) = S(k) again, so the
    # wrap from ph = 1 back to 0 shows exactly the same picture and nothing snaps.
    def steps_at(kq):
        kq = np.where(kq > KS[-1], kq - 1.0, kq)                 # beyond the last measured edge: same phase, one step up
        kq = np.where(kq < k_of_y(Y0) + 0.02, kq + 1.0, kq)      # at the far landing: same phase, one step down
        ys = y_of_k(kq); xs = CX + (sx - CX) * hw(ys) / hw(sy)
        return sample(plate, xs, ys)
    A = steps_at(kk - ph); C = steps_at(kk - ph + 1.0)
    moving = A * (1.0 - ph) + C * ph
    frame = np.where(inband[..., None], moving, static)
    im = Image.fromarray(np.clip(frame, 0, 255).astype(np.uint8)).convert("RGBA")
    # box: arrives at its photographed spot at the end
    kb = K_BOX - SPEED * (secs - t)
    yb = float(y_of_k(kb)); sc = float(hw(yb) / hw(BOX_BOTTOM_Y))
    w = box.width * sc * s; h = box.height * sc * s
    if w > 3:
        spr = box.resize((max(1, round(w)), max(1, round(h))), Image.LANCZOS)
        # darker and a bit softer far away (station light falls off down the shaft)
        dim = 0.78 + 0.22 * min(1.0, sc)
        r_, g_, b_, a_ = spr.split()
        spr = Image.merge("RGBA", (r_.point(lambda v: v * dim), g_.point(lambda v: v * dim), b_.point(lambda v: v * dim * 0.97), a_))
        cxb = CX + (BOX_CX - CX) * sc
        X = (cxb - CXL) * s - w / 2; Ybot = yb * s + (2226 - BOX_BOTTOM_Y) * sc * s
        Y = Ybot - h
        sh = Image.new("RGBA", (int(w * 1.3) + 2, int(h * 0.35) + 2), (0, 0, 0, 0))
        ImageDraw.Draw(sh).ellipse((0, 0, sh.width - 1, sh.height - 1), fill=(15, 12, 10, 110))
        sh = sh.filter(ImageFilter.GaussianBlur(max(1, w * 0.05)))
        im.alpha_composite(sh, (int(X - w * 0.15), int(Ybot - h * 0.22)))
        im.alpha_composite(spr, (int(round(X)), int(round(Y))))
    im.convert("RGB").save(os.path.join(outdir, f"f{f:04d}.png"))
    if f % 60 == 0: print("frame", f, "/", n, flush=True)
