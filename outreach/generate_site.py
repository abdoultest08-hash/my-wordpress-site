"""
generate_site.py
Generates a custom HTML mock website for a given business lead using Claude API.
"""
import anthropic
import os
import re

client = anthropic.Anthropic(api_key=os.environ["ANTHROPIC_API_KEY"])

# Industry → hero image URL mapping (Unsplash, no key needed)
INDUSTRY_IMAGES = {
    "plumber":        "https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=1800&auto=format&fit=crop&q=80",
    "landscaper":     "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1800&auto=format&fit=crop&q=80",
    "house cleaner":  "https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1800&auto=format&fit=crop&q=80",
    "hvac technician":"https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1800&auto=format&fit=crop&q=80",
    "pest control":   "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1800&auto=format&fit=crop&q=80",
    "auto detailer":  "https://images.unsplash.com/photo-1520340356584-f9917d1eea6f?w=1800&auto=format&fit=crop&q=80",
    "painter":        "https://images.unsplash.com/photo-1562259929-b4e1fd3aef09?w=1800&auto=format&fit=crop&q=80",
    "electrician":    "https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=1800&auto=format&fit=crop&q=80",
    "appliance repair":"https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=1800&auto=format&fit=crop&q=80",
    "gutter installer":"https://images.unsplash.com/photo-1584467735815-8234900e7f82?w=1800&auto=format&fit=crop&q=80",
    "roofer":         "https://images.unsplash.com/photo-1635424710928-0544e8512eae?w=1800&auto=format&fit=crop&q=80",
    "default":        "https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1800&auto=format&fit=crop&q=80",
}

def get_hero_image(industry: str) -> str:
    industry_lower = industry.lower()
    for key, url in INDUSTRY_IMAGES.items():
        if key in industry_lower:
            return url
    return INDUSTRY_IMAGES["default"]


SYSTEM_PROMPT = """You are an expert web designer who creates beautiful, professional HTML mockup websites for local service businesses.
You generate complete, self-contained single-file HTML pages.
Rules:
- Use inline CSS only (no external CSS files)
- Use Google Fonts via CDN
- Use Unsplash images via URL (already provided)
- Make it look REAL and professional — the business owner should be impressed
- Keep the color scheme consistent (navy + gold accent works great)
- Output ONLY the raw HTML — no markdown, no explanation, no code fences
"""

def generate_mock_site(lead: dict) -> str:
    """Generate a full HTML mock website for a lead using Claude API."""
    hero_img = get_hero_image(lead.get("industry", ""))
    phone    = lead.get("phone", "(555) 000-0000")
    city     = lead.get("city", "Your City")
    state    = lead.get("state", "CA")

    prompt = f"""Create a professional, modern single-page HTML website mockup for this local business:

Business Name: {lead['business_name']}
Industry: {lead['industry']}
City: {city}, {state}
Phone: {phone}

Hero background image URL (use this exactly): {hero_img}

Requirements:
1. Sticky dark navy navbar with business name/logo, nav links, and a gold CTA button "Get Free Quote"
2. Full-screen hero with dark overlay, compelling headline for a {lead['industry']} business, subtext, phone number, and a white lead-capture form card on the right ("Get a Free Estimate" with name, phone, service dropdown, gold submit button)
3. "Our Services" section — 3 relevant service cards with icons for a {lead['industry']} business
4. "Why Choose Us" section with 4 trust bullet points (licensed, insured, local, etc.)
5. 3 fake 5-star Google reviews from local customers
6. A dark CTA banner: "Ready to get started? Call us today"
7. Simple footer with business name, phone ({phone}), email, and {city} {state}

Color scheme:
- Primary/navbar: #0D1B2A (dark navy)
- Accent/buttons: #F5A623 (gold)
- White backgrounds for cards
- Inter font for body, Poppins for headings (load from Google Fonts CDN)

Output ONLY the complete HTML. No explanation."""

    message = client.messages.create(
        model="claude-sonnet-4-6",
        max_tokens=8000,
        system=SYSTEM_PROMPT,
        messages=[{"role": "user", "content": prompt}]
    )

    html = message.content[0].text.strip()
    # Strip markdown fences if model accidentally includes them
    html = re.sub(r'^```html?\n?', '', html, flags=re.MULTILINE)
    html = re.sub(r'\n?```$', '',   html, flags=re.MULTILINE)
    return html


if __name__ == "__main__":
    # Quick test
    test_lead = {
        "business_name": "Rodriguez Plumbing",
        "owner_name":    "Carlos",
        "industry":      "plumber",
        "city":          "Los Angeles",
        "state":         "CA",
        "email":         "carlos@example.com",
        "phone":         "(213) 555-0101",
    }
    html = generate_mock_site(test_lead)
    with open("test_output.html", "w") as f:
        f.write(html)
    print(f"Generated {len(html)} chars → test_output.html")
