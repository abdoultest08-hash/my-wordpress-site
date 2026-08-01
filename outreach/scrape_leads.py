#!/usr/bin/env python3
"""
scrape_leads.py — Google Maps lead scraper
─────────────────────────────────────────────
Searches Google Maps for local businesses in a given niche/city,
extracts business name, address, phone, website status, and email
(where visible on the GMB listing or website contact page),
then writes results to an Excel file ready for pipeline.py.

Usage:
  pip install playwright openpyxl
  playwright install chromium

  python3 scrape_leads.py --niche "accountants" --city "Manchester" --limit 50
  python3 scrape_leads.py --niche "home care" --city "Birmingham" --limit 30
  python3 scrape_leads.py --niche "plumber" --city "Leeds" --limit 40 --out leads_plumbers_leeds.xlsx

Flags:
  --niche TEXT     Industry to search (e.g. "plumber", "care agency", "accountant")
  --city  TEXT     City or area to search in
  --limit N        Max results to collect (default 50)
  --out   FILE     Output xlsx filename (default: leads_<niche>_<city>.xlsx)
  --headless       Run browser in headless mode (default: True)
"""
import argparse
import re
import time
import random
import openpyxl
from pathlib import Path
from datetime import datetime

try:
    from playwright.sync_api import sync_playwright, TimeoutError as PWTimeout
except ImportError:
    print("❌ Playwright not installed. Run: pip install playwright && playwright install chromium")
    raise


EMAIL_RE = re.compile(r"[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}")
BASE     = Path(__file__).parent


def slug(text: str) -> str:
    return re.sub(r"[^a-z0-9]+", "_", text.lower()).strip("_")


def extract_emails_from_page(page) -> list[str]:
    """Scrape visible email addresses from the current page."""
    text = page.inner_text("body") or ""
    return list(set(EMAIL_RE.findall(text)))


def find_email_on_website(page, website_url: str) -> str:
    """Visit website, try /contact page, return first business email found."""
    skip_domains = {"gmail.com", "yahoo.com", "hotmail.com", "outlook.com", "example.com"}
    try:
        page.goto(website_url, timeout=15_000, wait_until="domcontentloaded")
        emails = extract_emails_from_page(page)
        # Try /contact page if no email found
        if not emails:
            for path in ["/contact", "/contact-us", "/get-in-touch", "/about"]:
                try:
                    page.goto(website_url.rstrip("/") + path, timeout=10_000, wait_until="domcontentloaded")
                    emails = extract_emails_from_page(page)
                    if emails:
                        break
                except Exception:
                    continue
        # Filter out generic/free providers
        business_emails = [e for e in emails if e.split("@")[-1] not in skip_domains]
        return business_emails[0] if business_emails else (emails[0] if emails else "")
    except Exception:
        return ""


def scrape_google_maps(niche: str, city: str, limit: int, headless: bool = True) -> list[dict]:
    query   = f"{niche} in {city}"
    results = []

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=headless)
        ctx     = browser.new_context(viewport={"width": 1280, "height": 900})
        page    = ctx.new_page()

        print(f"  → Searching Google Maps: '{query}'")
        page.goto(f"https://www.google.com/maps/search/{query.replace(' ', '+')}")
        time.sleep(3)

        # Dismiss cookie consent if present
        for btn_text in ["Accept all", "Reject all", "Accept"]:
            try:
                page.get_by_role("button", name=btn_text).click(timeout=3000)
                break
            except Exception:
                pass

        # Scroll the results panel to load more listings
        results_panel = page.locator('div[role="feed"]')
        prev_count = 0
        for _ in range(20):
            listing_els = page.locator('a[href*="/maps/place/"]').all()
            count = len(listing_els)
            if count >= limit or count == prev_count:
                break
            prev_count = count
            try:
                results_panel.evaluate("el => el.scrollBy(0, 800)")
            except Exception:
                page.mouse.wheel(0, 800)
            time.sleep(random.uniform(1.0, 2.0))

        listing_els = page.locator('a[href*="/maps/place/"]').all()[:limit]
        hrefs = [el.get_attribute("href") for el in listing_els if el.get_attribute("href")]
        hrefs = list(dict.fromkeys(hrefs))[:limit]  # deduplicate, preserve order

        print(f"  → Found {len(hrefs)} listings — extracting details...")

        for i, href in enumerate(hrefs, 1):
            try:
                page.goto(href, timeout=20_000, wait_until="domcontentloaded")
                time.sleep(random.uniform(1.5, 2.5))

                # Business name
                name = ""
                for sel in ['h1[data-attrid]', 'h1.DUwDvf', 'h1']:
                    el = page.locator(sel).first
                    if el.count():
                        name = el.inner_text().strip()
                        break

                # Address
                address = ""
                addr_el = page.locator('button[data-item-id="address"]').first
                if addr_el.count():
                    address = addr_el.inner_text().strip()

                # Phone
                phone = ""
                phone_el = page.locator('button[data-item-id^="phone:tel"]').first
                if phone_el.count():
                    phone = phone_el.inner_text().strip()

                # Website
                website = ""
                web_el = page.locator('a[data-item-id="authority"]').first
                if web_el.count():
                    website = web_el.get_attribute("href") or ""

                # Email — try website contact page
                email = ""
                if website:
                    email = find_email_on_website(ctx.new_page(), website)

                # GMB URL for CRM
                gmb_url = href

                lead = {
                    "business_name": name,
                    "industry":      niche,
                    "city":          city,
                    "address":       address,
                    "phone":         phone,
                    "email":         email,
                    "website":       website,
                    "gmb_url":       gmb_url,
                    "pitch_type":    "upgrade" if website else "new",
                    "notes":         "",
                }
                results.append(lead)
                status = f"✓ {name[:40]}" if name else "✓ (unnamed)"
                email_status = f" — {email}" if email else " — no email"
                print(f"    [{i}/{len(hrefs)}] {status}{email_status}")

            except Exception as e:
                print(f"    [{i}/{len(hrefs)}] ✗ Error: {e}")
                continue

        browser.close()

    return results


def write_xlsx(leads: list[dict], out_path: Path):
    wb = openpyxl.Workbook()
    ws = wb.active
    ws.title = "Leads"

    cols = ["business_name", "industry", "city", "address", "phone",
            "email", "website", "gmb_url", "pitch_type", "notes"]
    ws.append(cols)

    for lead in leads:
        ws.append([lead.get(c, "") for c in cols])

    # Auto-width
    for col in ws.columns:
        max_len = max((len(str(cell.value or "")) for cell in col), default=10)
        ws.column_dimensions[col[0].column_letter].width = min(max_len + 2, 50)

    wb.save(out_path)


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--niche",     required=True)
    parser.add_argument("--city",      required=True)
    parser.add_argument("--limit",     type=int, default=50)
    parser.add_argument("--out",       default="")
    parser.add_argument("--headless",  action="store_true", default=True)
    args = parser.parse_args()

    out_file = args.out or f"leads_{slug(args.niche)}_{slug(args.city)}.xlsx"
    out_path = BASE / out_file

    print(f"\n🔍 Scraping Google Maps")
    print(f"   Niche : {args.niche}")
    print(f"   City  : {args.city}")
    print(f"   Limit : {args.limit}")
    print(f"   Output: {out_path}\n")

    leads = scrape_google_maps(args.niche, args.city, args.limit, headless=args.headless)

    with_email    = [l for l in leads if l["email"]]
    without_email = [l for l in leads if not l["email"]]

    print(f"\n📊 Results:")
    print(f"   Total scraped : {len(leads)}")
    print(f"   With email    : {len(with_email)}")
    print(f"   Without email : {len(without_email)} (may need manual research)")

    write_xlsx(leads, out_path)
    print(f"\n✅ Saved to {out_path}")
    print(f"   Next: python3 pipeline.py --leads {out_file} --limit 20 --draft")
