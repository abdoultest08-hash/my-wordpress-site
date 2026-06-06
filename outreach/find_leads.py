#!/usr/bin/env python3
"""
find_leads.py — Automatic Lead Finder
──────────────────────────────────────
Searches Google Maps Places API for local service businesses
that have NO website, then saves them to an Excel file ready
for the outreach pipeline.

Requirements:
  pip install requests openpyxl

Setup:
  Get a free Google Maps API key:
  1. Go to console.cloud.google.com
  2. Create project → Enable "Places API" (New)
  3. APIs & Services → Credentials → Create API Key
  4. Add to .env:  GOOGLE_MAPS_KEY=your_key_here
  Free tier: $200/month credit = ~5,000 searches free

Usage:
  python3 find_leads.py --industry "plumber" --city "Manchester" --limit 20
  python3 find_leads.py --industry "cleaner" --city "Los Angeles, CA" --limit 10 --output my_leads.xlsx
"""
import argparse
import os
import time
import requests
import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment
from pathlib import Path
from datetime import date

MAPS_KEY = os.environ.get("GOOGLE_MAPS_KEY", "")
BASE     = Path(__file__).parent

HEADERS = [
    "business_name", "owner_name", "industry", "city", "state",
    "email", "phone", "address", "logo_url", "gmb_url", "notes",
]


def search_places(query: str, location: str) -> list[dict]:
    """Text search for businesses via Places API (New)."""
    url  = "https://places.googleapis.com/v1/places:searchText"
    body = {
        "textQuery":      f"{query} in {location}",
        "maxResultCount": 20,
        "languageCode":   "en",
    }
    fields = ",".join([
        "places.id",
        "places.displayName",
        "places.formattedAddress",
        "places.nationalPhoneNumber",
        "places.internationalPhoneNumber",
        "places.websiteUri",
        "places.googleMapsUri",
        "places.businessStatus",
        "places.types",
        "places.editorialSummary",
        "places.photos",
        "places.rating",
        "places.userRatingCount",
    ])
    headers = {
        "Content-Type":     "application/json",
        "X-Goog-Api-Key":   MAPS_KEY,
        "X-Goog-FieldMask": fields,
    }
    r = requests.post(url, json=body, headers=headers)
    r.raise_for_status()
    return r.json().get("places", [])


def get_photo_url(photo_name: str, max_width: int = 400) -> str:
    """Get a photo URL from a Places photo reference."""
    if not photo_name:
        return ""
    return (
        f"https://places.googleapis.com/v1/{photo_name}/media"
        f"?maxWidthPx={max_width}&key={MAPS_KEY}"
    )


def parse_city_state(address: str, fallback_city: str) -> tuple[str, str]:
    """Try to extract city and state/country from a formatted address."""
    parts = [p.strip() for p in address.split(",")]
    if len(parts) >= 3:
        # UK: "street, city, postcode" or US: "street, city, state zip"
        city  = parts[-2] if len(parts) >= 2 else fallback_city
        state = parts[-1].split()[0] if parts[-1] else ""
        return city, state
    elif len(parts) == 2:
        return parts[0], parts[1]
    return fallback_city, ""


def find_leads(industry: str, city: str, limit: int) -> list[dict]:
    """Search for businesses without websites and return as lead dicts."""
    print(f"\n🔍 Searching for '{industry}' businesses in {city}...")

    if not MAPS_KEY:
        print("❌ GOOGLE_MAPS_KEY not set in .env")
        print("   Get a free key: console.cloud.google.com → Enable Places API")
        return []

    all_places  = []
    seen_ids    = set()

    # Run a few query variations to get more results
    queries = [
        industry,
        f"{industry} service",
        f"local {industry}",
    ]

    for q in queries:
        try:
            places = search_places(q, city)
            for p in places:
                pid = p.get("id")
                if pid and pid not in seen_ids:
                    seen_ids.add(pid)
                    all_places.append(p)
        except Exception as e:
            print(f"  ⚠ Query '{q}' failed: {e}")
        time.sleep(0.3)

    print(f"  Found {len(all_places)} total businesses")

    leads = []
    no_site_count = 0

    for place in all_places:
        if len(leads) >= limit:
            break

        # Skip if they already have a website
        website = place.get("websiteUri", "")
        if website:
            continue

        # Skip permanently closed
        if place.get("businessStatus") == "PERMANENTLY_CLOSED":
            continue

        no_site_count += 1
        name    = place.get("displayName", {}).get("text", "")
        address = place.get("formattedAddress", "")
        phone   = place.get("nationalPhoneNumber") or place.get("internationalPhoneNumber", "")
        gmb_url = place.get("googleMapsUri", "")
        summary = place.get("editorialSummary", {}).get("text", "")
        rating  = place.get("rating", "")
        reviews = place.get("userRatingCount", "")

        # Try to get a logo/photo from the first photo
        photos   = place.get("photos", [])
        logo_url = get_photo_url(photos[0].get("name", "")) if photos else ""

        city_parsed, state_parsed = parse_city_state(address, city)

        notes_parts = []
        if summary:
            notes_parts.append(summary)
        if rating and reviews:
            notes_parts.append(f"{rating}★ ({reviews} reviews on Google)")
        notes = ". ".join(notes_parts)

        leads.append({
            "business_name": name,
            "owner_name":    "",
            "industry":      industry,
            "city":          city_parsed,
            "state":         state_parsed,
            "email":         "",
            "phone":         phone,
            "address":       address,
            "logo_url":      logo_url,
            "gmb_url":       gmb_url,
            "notes":         notes,
        })

        print(f"  ✓ {name} — no website | {phone}")

    print(f"\n  {no_site_count} businesses found with NO website → keeping {len(leads)}")
    return leads


def save_to_excel(leads: list[dict], output_path: Path):
    """Save leads list to a formatted Excel file."""
    wb = openpyxl.Workbook()
    ws = wb.active
    ws.title = "Leads"

    header_fill = PatternFill(start_color="0D1B2A", end_color="0D1B2A", fill_type="solid")
    header_font = Font(color="FFFFFF", bold=True)

    for col, h in enumerate(HEADERS, 1):
        cell = ws.cell(row=1, column=col, value=h)
        cell.fill = header_fill
        cell.font = header_font
        cell.alignment = Alignment(horizontal="center")

    for lead in leads:
        ws.append([lead.get(h, "") for h in HEADERS])

    widths = [22, 18, 14, 14, 8, 26, 16, 34, 40, 40, 50]
    for col, w in enumerate(widths, 1):
        ws.column_dimensions[ws.cell(row=1, column=col).column_letter].width = w

    wb.save(output_path)
    print(f"\n✅ Saved {len(leads)} leads → {output_path}")


def main():
    parser = argparse.ArgumentParser(description="Find local businesses with no website")
    parser.add_argument("--industry", required=True, help='e.g. "plumber", "cleaner", "landscaper"')
    parser.add_argument("--city",     required=True, help='e.g. "Manchester", "Los Angeles CA"')
    parser.add_argument("--limit",    type=int, default=20, help="Max leads to collect (default 20)")
    parser.add_argument("--output",   default="", help="Output Excel filename (default: auto-named)")
    args = parser.parse_args()

    leads = find_leads(args.industry, args.city, args.limit)

    if not leads:
        print("No leads found. Try a different industry or city.")
        return

    today      = date.today().isoformat()
    city_slug  = args.city.lower().replace(" ", "_").replace(",", "")
    ind_slug   = args.industry.lower().replace(" ", "_")
    out_name   = args.output or f"leads_{ind_slug}_{city_slug}_{today}.xlsx"
    out_path   = BASE / out_name

    save_to_excel(leads, out_path)
    print(f"\n📋 Next: review the file, add any emails you find, then run:")
    print(f"   python3 pipeline.py --leads {out_name} --limit 10 --draft")


if __name__ == "__main__":
    main()
