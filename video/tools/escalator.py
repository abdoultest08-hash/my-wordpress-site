"""Render the top-down escalator: steps glide upwards with correct perspective, packages ride along.
usage: escalator.py STILL SPRITE_DIR OUT_DIR SECONDS FPS"""
import sys, os
import numpy as np
from PIL import Image, ImageFilter
still, sprites, outdir, secs, fps = sys.argv[1], sys.argv[2], sys.argv[3], float(sys.argv[4]), int(sys.argv[5])
os.makedirs(outdir, exist_ok=True)
src = np.array(Image.open(still).convert("RGB")).astype(np.float32)
SH, SW, _ = src.shape                     # 2736 x 1536
OW, OH = 1080, 1920; s = OW / SW

# measured geometry (source px): step edges (top->bottom), band centre/width as a function of y
edges = np.array([195, 401, 613, 829, 1054, 1292, 1538, 1783, 2046, 2315, 2599], float)
uk = np.arange(len(edges))[::-1].astype(float)       # u increases upwards: bottom edge u=0
def u_of_y(y):
    y = np.asarray(y, float)
    u = np.interp(y, edges, uk)
    top_g, bot_g = edges[1] - edges[0], edges[-1] - edges[-2]
    u = np.where(y < edges[0], uk[0] + (edges[0] - y) / top_g, u)
    u = np.where(y > edges[-1], uk[-1] - (y - edges[-1]) / bot_g, u)
    return u
def y_of_u(u):
    u = np.asarray(u, float)
    y = np.interp(-u, -uk, edges)
    top_g, bot_g = edges[1] - edges[0], edges[-1] - edges[-2]
    y = np.where(u > uk[0], edges[0] - (u - uk[0]) * top_g, y)
    y = np.where(u < uk[-1], edges[-1] + (uk[-1] - u) * bot_g, y)
    return y
cx = lambda y: 761.0 - 0.0073 * y
wd = lambda y: 728.0 + 0.0969 * y

SPEED = 0.85                                  # steps per second: calm
yy, xx = np.mgrid[0:OH, 0:OW].astype(np.float32)
ys, xs = yy / s, xx / s                       # output pixel -> source coords (static)
u_out = u_of_y(ys)
c_out, w_out = cx(ys), wd(ys)
inband = np.abs(xs - c_out) < (w_out / 2 - 2)
U_MIN = u_of_y(SH - 1)

def bilinear(img, x, y):
    x = np.clip(x, 0, SW - 1.001); y = np.clip(y, 0, SH - 1.001)
    x0 = np.floor(x).astype(int); y0 = np.floor(y).astype(int); fx = (x - x0)[..., None]; fy = (y - y0)[..., None]
    a = img[y0, x0]; b = img[y0, x0 + 1]; c = img[y0 + 1, x0]; d = img[y0 + 1, x0 + 1]
    return (a * (1 - fx) + b * fx) * (1 - fy) + (c * (1 - fx) + d * fx) * fy

static = bilinear(src, xs, ys)

# packages: (sprite, lane offset as fraction of band width, starting step coordinate at t=0, depth in steps, width fraction)
def load(n):
    return Image.open(os.path.join(sprites, n)).convert("RGBA")
spr = {k: load(k + ".png") for k in ("lid-white", "lid-black", "bag1", "bag2")}
items = [
    ("lid-white", -0.22, -3.2, 0.95, 0.38), ("bag1", 0.21, -3.35, 1.05, 0.36),
    ("lid-black", 0.22, -5.3, 0.95, 0.38), ("bag2", -0.21, -5.15, 1.05, 0.36),
    ("bag1", -0.2, -7.3, 1.05, 0.36), ("lid-white", 0.22, -7.45, 0.95, 0.38),
    ("lid-black", -0.22, -9.3, 0.95, 0.38), ("bag2", 0.2, -9.4, 1.05, 0.36),
]

n = int(round(secs * fps))
for f in range(n):
    t = f / fps
    phase = (SPEED * t) % 1.0
    us = u_out - phase
    us = np.where(us < U_MIN, us + 1.0, us)           # wrap: the step one above looks identical
    ysrc = y_of_u(us)
    xsrc = cx(ysrc) + (xs - c_out) * wd(ysrc) / w_out
    moving = bilinear(src, xsrc, ysrc)
    frame = np.where(inband[..., None], moving, static)
    im = Image.fromarray(np.clip(frame, 0, 255).astype(np.uint8)).convert("RGBA")
    # packages ride on the steps (draw far ones first)
    placed = []
    for name, lane, u0, depth, wf in items:
        ub = u0 + SPEED * t
        yb, yt = float(y_of_u(ub)), float(y_of_u(ub + depth))   # bottom and top edge on screen (source px)
        if yb < -50 or yt > SH + 50: continue
        ym = (yb + yt) / 2
        bw = wf * wd(ym); bh = yb - yt
        x_c = cx(ym) + lane * wd(ym)
        placed.append((yt, name, x_c, yt, bw, bh))
    for _, name, x_c, yt, bw, bh in sorted(placed):
        W_, H_ = max(2, int(bw * s)), max(2, int(bh * s))
        sp = spr[name].resize((W_, H_), Image.LANCZOS)
        X, Y = int((x_c - bw / 2) * s), int(yt * s)
        sh = Image.new("RGBA", (W_ + 40, H_ + 40), (0, 0, 0, 0))
        sh.paste(Image.new("RGBA", (W_, H_), (20, 16, 12, 120)), (20, 26), sp.split()[3])
        sh = sh.filter(ImageFilter.GaussianBlur(9))
        im.alpha_composite(sh, (max(0, X - 20), max(0, Y - 20))) if X - 20 >= 0 and Y - 20 >= 0 else None
        im.alpha_composite(sp, (X, Y)) if X >= 0 and Y >= 0 and X + W_ <= OW and Y + H_ <= OH else im.paste(sp, (X, Y), sp)
    im.convert("RGB").save(os.path.join(outdir, f"f{f:04d}.png"))
    if f % 60 == 0: print("frame", f, "/", n, flush=True)
