import sys
from PIL import Image, ImageDraw, ImageFilter
logo_black, out = sys.argv[1], sys.argv[2]
logo = Image.open(logo_black).convert("RGBA")
def lid(fill, edge, ink, name):
    W, H = 720, 540
    im = Image.new("RGBA", (W, H), (0, 0, 0, 0)); d = ImageDraw.Draw(im)
    d.rounded_rectangle((0, 0, W - 1, H - 1), radius=14, fill=edge)            # lid edge / side
    d.rounded_rectangle((10, 10, W - 11, H - 11), radius=10, fill=fill)         # lid top
    # soft top-light gradient across the lid
    g = Image.new("L", (W, H), 0); gd = ImageDraw.Draw(g)
    for i in range(H): gd.line([(0, i), (W, i)], fill=int(26 * (1 - i / H)))
    im = Image.composite(Image.new("RGBA", (W, H), (255, 255, 255, 255)), im, g.point(lambda v: v)) if fill[0] < 128 else im
    lw = 430; L = logo.resize((lw, round(logo.height * lw / logo.width)), Image.LANCZOS)
    r, g2, b, a = L.split()
    L = Image.merge("RGBA", (r.point(lambda v: ink[0]), g2.point(lambda v: ink[1]), b.point(lambda v: ink[2]), a.point(lambda v: int(v * 0.92))))
    im.alpha_composite(L, ((W - lw) // 2, (H - L.height) // 2))
    im.save(f"{out}/{name}.png")
lid((246, 243, 238, 255), (214, 209, 201, 255), (30, 27, 25), "lid-white")
lid((26, 25, 24, 255), (8, 8, 8, 255), (226, 220, 210), "lid-black")
