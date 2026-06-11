"""
hero_images.py
Generates realistic-looking hero background images for each industry
using gradients + geometric overlays. Saves to hero_images/ directory.
No external network required.
"""
from PIL import Image, ImageDraw, ImageFilter
import math, os, hashlib

OUT_DIR = os.path.join(os.path.dirname(__file__), "hero_images")
os.makedirs(OUT_DIR, exist_ok=True)

W, H = 1440, 900

# Industry → (dark colour, mid colour, light accent) — professional, photo-like gradients
INDUSTRY_PALETTES = {
    "plumber":      ((10, 30, 60),   (20, 55, 100),  (40, 100, 160)),
    "plumbing":     ((10, 30, 60),   (20, 55, 100),  (40, 100, 160)),
    "electrician":  ((15, 25, 50),   (30, 50, 90),   (60, 90, 140)),
    "electrical":   ((15, 25, 50),   (30, 50, 90),   (60, 90, 140)),
    "roofer":       ((30, 20, 10),   (70, 45, 20),   (110, 75, 35)),
    "roofing":      ((30, 20, 10),   (70, 45, 20),   (110, 75, 35)),
    "landscap":     ((10, 35, 15),   (20, 65, 25),   (35, 100, 45)),
    "lawn":         ((10, 35, 15),   (20, 65, 25),   (35, 100, 45)),
    "tree":         ((10, 35, 15),   (20, 65, 25),   (35, 100, 45)),
    "clean":        ((15, 45, 55),   (25, 80, 100),  (40, 120, 145)),
    "maid":         ((15, 45, 55),   (25, 80, 100),  (40, 120, 145)),
    "hvac":         ((20, 30, 50),   (35, 55, 90),   (55, 85, 130)),
    "air":          ((20, 30, 50),   (35, 55, 90),   (55, 85, 130)),
    "heat":         ((20, 30, 50),   (35, 55, 90),   (55, 85, 130)),
    "pest":         ((25, 35, 20),   (45, 60, 35),   (70, 90, 50)),
    "paint":        ((40, 20, 50),   (70, 35, 90),   (100, 55, 130)),
    "detailing":    ((20, 20, 35),   (35, 35, 65),   (55, 55, 100)),
    "auto":         ((20, 20, 35),   (35, 35, 65),   (55, 55, 100)),
    "floor":        ((35, 25, 15),   (65, 50, 30),   (95, 75, 45)),
    "handyman":     ((25, 30, 35),   (45, 55, 65),   (70, 85, 100)),
    "remodel":      ((25, 30, 35),   (45, 55, 65),   (70, 85, 100)),
    "construct":    ((25, 30, 35),   (45, 55, 65),   (70, 85, 100)),
    "default":      ((13, 33, 55),   (25, 60, 100),  (45, 95, 145)),
}


def get_palette(industry: str):
    low = industry.lower()
    for key, pal in INDUSTRY_PALETTES.items():
        if key in low:
            return pal
    return INDUSTRY_PALETTES["default"]


def lerp_colour(c1, c2, t):
    return tuple(int(c1[i] + (c2[i] - c1[i]) * t) for i in range(3))


def find_real_photo(industry: str) -> str | None:
    """Return path to a real photo for this industry, or None."""
    low = industry.lower()
    mapping = [
        (["plumb"],                          ["plumber", "plumbing"]),
        (["roof", "gutter"],                 ["roof", "roofer"]),
        (["electr"],                         ["electric", "electrician"]),
        (["clean", "maid"],                  ["clean", "cleaning"]),
        (["lands", "lawn", "tree"],          ["landscap", "landscaper"]),
        (["hvac", "heat", "air", "gas"],     ["hvac"]),
        (["handy", "remod", "construct"],    ["handyman"]),
        (["paint"],                          ["paint", "painter"]),
    ]
    for keywords, filenames in mapping:
        if any(k in low for k in keywords):
            for fname in filenames:
                path = os.path.join(OUT_DIR, f"{fname}.jpg")
                if os.path.exists(path):
                    return path
    return None


def make_hero(industry: str) -> str:
    """Return real photo if available, otherwise generate gradient fallback."""
    real = find_real_photo(industry)
    if real:
        return real

    slug = industry.lower().replace(" ", "_")[:20]
    out_path = os.path.join(OUT_DIR, f"{slug}.jpg")
    if os.path.exists(out_path):
        return out_path

    dark, mid, light = get_palette(industry)
    img = Image.new("RGB", (W, H))
    draw = ImageDraw.Draw(img)

    # Diagonal gradient: dark bottom-left → mid → light top-right
    for x in range(W):
        for y in range(H):
            t = (x / W * 0.6 + (H - y) / H * 0.4)
            if t < 0.5:
                colour = lerp_colour(dark, mid, t * 2)
            else:
                colour = lerp_colour(mid, light, (t - 0.5) * 2)
            img.putpixel((x, y), colour)

    # Subtle geometric lines (diagonal hatching for depth)
    line_colour = tuple(min(255, c + 15) for c in mid)
    for i in range(-H, W + H, 80):
        draw.line([(i, 0), (i + H, H)], fill=line_colour, width=1)

    # Soft vignette — darken edges
    vignette = Image.new("RGB", (W, H), (0, 0, 0))
    vdraw = ImageDraw.Draw(vignette)
    for r in range(min(W, H) // 2, 0, -1):
        alpha = int(120 * (1 - r / (min(W, H) / 2)))
        vdraw.ellipse([W//2 - r, H//2 - r, W//2 + r, H//2 + r],
                      fill=(0, 0, 0, 0), outline=None)

    # Blur for smoothness
    img = img.filter(ImageFilter.GaussianBlur(radius=2))

    img.save(out_path, "JPEG", quality=90)
    return out_path


if __name__ == "__main__":
    for ind in ["plumber", "roofer", "landscaper", "electrician", "cleaning", "hvac", "painter"]:
        p = make_hero(ind)
        print(f"  {ind}: {p}")
