"""
generate_site.py
Generates a custom HTML mock website for a given business lead using Claude API.
Layout: AA California Roofing style — navbar, hero with right-side form card, services strip.
Neutral professional colour scheme that works across all industries.
"""
import anthropic
import os
import re

client = anthropic.Anthropic(api_key=os.environ["ANTHROPIC_API_KEY"])

# Industry → Unsplash hero image (landscape, 1800px wide)
INDUSTRY_IMAGES = {
    "plumber":        "https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=1800&auto=format&fit=crop&q=80",
    "plumbing":       "https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=1800&auto=format&fit=crop&q=80",
    "landscap":       "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1800&auto=format&fit=crop&q=80",
    "lawn":           "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1800&auto=format&fit=crop&q=80",
    "tree":           "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1800&auto=format&fit=crop&q=80",
    "clean":          "https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1800&auto=format&fit=crop&q=80",
    "maid":           "https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1800&auto=format&fit=crop&q=80",
    "hvac":           "https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1800&auto=format&fit=crop&q=80",
    "air condition":  "https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1800&auto=format&fit=crop&q=80",
    "heat":           "https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1800&auto=format&fit=crop&q=80",
    "pest":           "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1800&auto=format&fit=crop&q=80",
    "detailing":      "https://images.unsplash.com/photo-1520340356584-f9917d1eea6f?w=1800&auto=format&fit=crop&q=80",
    "auto":           "https://images.unsplash.com/photo-1520340356584-f9917d1eea6f?w=1800&auto=format&fit=crop&q=80",
    "paint":          "https://images.unsplash.com/photo-1562259929-b4e1fd3aef09?w=1800&auto=format&fit=crop&q=80",
    "electric":       "https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=1800&auto=format&fit=crop&q=80",
    "appliance":      "https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=1800&auto=format&fit=crop&q=80",
    "gutter":         "https://images.unsplash.com/photo-1584467735815-8234900e7f82?w=1800&auto=format&fit=crop&q=80",
    "roof":           "https://images.unsplash.com/photo-1635424710928-0544e8512eae?w=1800&auto=format&fit=crop&q=80",
    "locksmith":      "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1800&auto=format&fit=crop&q=80",
    "pressure":       "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
    "floor":          "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
    "handyman":       "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
    "remodel":        "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
    "construct":      "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
    "default":        "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
}

def get_hero_image(industry: str) -> str:
    low = industry.lower()
    for key, url in INDUSTRY_IMAGES.items():
        if key in low:
            return url
    return INDUSTRY_IMAGES["default"]


SYSTEM_PROMPT = """You are an expert web designer building HTML mockup websites for local service businesses.
You produce complete, self-contained single-file HTML — no external CSS files, no JavaScript frameworks.
Use Google Fonts via CDN link tag only.

STRICT layout rules (follow exactly):
1. NAVBAR: white background, full width, max-width 1440px. Logo image (if provided) or business name text top-left.
   Nav links (Services, About, Contact) centre. Phone number + solid CTA button top-right.
2. HERO: full-viewport-width background image with a dark semi-transparent overlay (rgba 0,0,0,0.45).
   Min-height 580px. Use flexbox row layout:
   LEFT SIDE (55% width): large white headline (2 lines), accent-coloured sub-headline,
   short description, phone CTA button with phone icon, star rating line ("★★★★★ [N] Five-Star Reviews").
   RIGHT SIDE (40% width): white rounded card (border-radius 12px, strong box-shadow) —
   dark pill badge at top (offer text), bold form title, 3-4 input fields styled with border,
   gold/accent full-width submit button.
3. SERVICES STRIP: light grey background (#F7F8FA), centred section heading + subtext,
   3 service cards in a row (icon + title + 1-line description). Padding max 48px top/bottom
   so it is fully visible at 900px viewport height.

Design tokens:
- Primary dark: #1B2A3B
- Accent: #E8A020 (warm amber)
- Background light: #F7F8FA
- Font: Inter (body), Poppins (headings) — load both from Google Fonts CDN
- Viewport width: 1440px — design for this width

Output ONLY raw HTML. No markdown, no code fences, no explanation."""


def generate_mock_site(lead: dict) -> str:
    hero_img = get_hero_image(lead.get("industry", ""))
    phone    = lead.get("phone", "(555) 000-0000")
    city     = lead.get("city", "Your City")
    state    = lead.get("state", "")
    address  = lead.get("address", "")
    logo_url = lead.get("logo_url", "")
    notes    = lead.get("notes", "")
    industry = lead.get("industry", "service")
    rating   = ""
    reviews  = ""

    # Extract rating/reviews from notes if Outscraper populated them
    import re as _re
    m = _re.search(r'([\d.]+)★.*?\((\d+)', notes or "")
    if m:
        rating  = m.group(1)
        reviews = m.group(2)
    star_line = f"★★★★★ {reviews} Five-Star Reviews" if reviews else "★★★★★ Trusted by Local Customers"

    location = f"{city}, {state}".strip(", ")

    logo_html = (
        f'<img src="{logo_url}" style="height:48px;width:auto;object-fit:contain" alt="logo">'
        if logo_url else
        f'<span style="font-family:Poppins,sans-serif;font-weight:700;font-size:18px;color:#1B2A3B">{lead["business_name"]}</span>'
    )

    notes_context = f"\nExtra context about this business: {notes}" if notes else ""

    prompt = f"""Build the HTML mockup for this business using EXACTLY the layout from the system instructions.

Business: {lead["business_name"]}
Industry: {industry}
Location: {location}
Phone: {phone}
Address: {address or location}
Star line: {star_line}
Hero image URL (use exactly as background-image): {hero_img}
Logo HTML (paste exactly into navbar top-left): {logo_html}{notes_context}

Write 3 services appropriate for a {industry} business in {city}.
Make the hero headline punchy and local (mention {city} or the trade).
The offer badge in the form card should reference the current month with a compelling offer (e.g. "Free Estimate — No Obligation").
Keep vertical padding tight so the services strip is visible without scrolling on a 900px tall screen.

Output ONLY the complete HTML."""

    message = client.messages.create(
        model="claude-sonnet-4-6",
        max_tokens=8000,
        system=SYSTEM_PROMPT,
        messages=[{"role": "user", "content": prompt}]
    )

    html = message.content[0].text.strip()
    html = re.sub(r'^```html?\n?', '', html, flags=re.MULTILINE)
    html = re.sub(r'\n?```$',      '', html, flags=re.MULTILINE)
    return html


if __name__ == "__main__":
    test_lead = {
        "business_name": "Rodriguez Plumbing",
        "industry":      "plumber",
        "city":          "Los Angeles",
        "state":         "CA",
        "phone":         "(213) 555-0101",
        "address":       "142 Main St, Los Angeles, CA 90012",
        "logo_url":      "",
        "notes":         "4.8★ (95 Google reviews) | Emergency callouts 24/7, established 2000",
    }
    html = generate_mock_site(test_lead)
    with open("test_output.html", "w") as f:
        f.write(html)
    print(f"Generated {len(html):,} chars → test_output.html")
