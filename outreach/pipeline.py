#!/usr/bin/env python3
"""
pipeline.py — AA Outreach Automation
─────────────────────────────────────
1. Reads leads from Excel (up to --limit leads, default 10)
2. Generates a custom HTML mock site per lead via Claude API
3. Screenshots the site with Puppeteer
4. Creates a Gmail draft with the screenshot embedded
5. Logs results to outreach_log.json

Usage:
  python3 pipeline.py --leads leads_example.xlsx --limit 10

Required env vars:
  ANTHROPIC_API_KEY   — your Anthropic API key
  YOUR_NAME           — your name (for email signature)
  YOUR_EMAIL          — your Gmail address
  YOUR_WEBSITE        — your website / portfolio link
"""
import argparse
import json
import os
import subprocess
import sys
import time
from datetime import datetime
from pathlib import Path

import openpyxl

from generate_site  import generate_mock_site
from gmail_draft    import create_draft

# ── Paths ────────────────────────────────────────────────────────────────────
BASE     = Path(__file__).parent
SITES    = BASE / "sites"
SHOTS    = BASE / "screenshots"
LOG_FILE = BASE / "outreach_log.json"
SHOT_JS  = BASE / "screenshot.js"

SITES.mkdir(exist_ok=True)
SHOTS.mkdir(exist_ok=True)


# ── Config ────────────────────────────────────────────────────────────────────
YOUR_NAME    = os.environ.get("YOUR_NAME",    "Alex")
YOUR_EMAIL   = os.environ.get("YOUR_EMAIL",   "")
YOUR_WEBSITE = os.environ.get("YOUR_WEBSITE", "yourwebsite.com")


# ── Helpers ───────────────────────────────────────────────────────────────────
def read_leads(xlsx_path: str, limit: int) -> list[dict]:
    wb = openpyxl.load_workbook(xlsx_path)
    ws = wb.active
    headers = [c.value for c in ws[1]]
    leads   = []
    for row in ws.iter_rows(min_row=2, values_only=True):
        if not any(row):
            continue
        lead = dict(zip(headers, row))
        # Skip already processed (check log)
        if not lead.get("email"):
            continue
        leads.append(lead)
        if len(leads) >= limit:
            break
    return leads


def load_log() -> dict:
    if LOG_FILE.exists():
        return json.loads(LOG_FILE.read_text())
    return {}


def save_log(log: dict):
    LOG_FILE.write_text(json.dumps(log, indent=2))


def slug(name: str) -> str:
    return "".join(c if c.isalnum() else "_" for c in name.lower()).strip("_")


def take_screenshot(html_path: Path, png_path: Path) -> bool:
    result = subprocess.run(
        ["node", str(SHOT_JS), str(html_path), str(png_path)],
        capture_output=True, text=True, cwd=str(BASE)
    )
    if result.returncode != 0:
        print(f"  ⚠ Screenshot error: {result.stderr[:200]}")
        return False
    return True


# ── Main pipeline ─────────────────────────────────────────────────────────────
def run(leads_file: str, limit: int, skip_gmail: bool = False):
    leads = read_leads(leads_file, limit)
    log   = load_log()

    if not leads:
        print("No leads found in file.")
        return

    print(f"\n🚀 Processing {len(leads)} leads from {leads_file}\n{'─'*50}")

    results = []
    for i, lead in enumerate(leads, 1):
        email = lead.get("email", "")
        name  = lead.get("business_name", f"Lead_{i}")
        key   = slug(name)

        print(f"\n[{i}/{len(leads)}] {name} <{email}>")

        # Skip if already done
        if key in log and log[key].get("status") == "draft_created":
            print(f"  ✓ Already processed — skipping")
            continue

        # 1. Generate HTML mock site
        print(f"  → Generating mock site with Claude...")
        try:
            html = generate_mock_site(lead)
        except Exception as e:
            print(f"  ✗ Site generation failed: {e}")
            log[key] = {"status": "error", "stage": "generate", "error": str(e), "ts": datetime.now().isoformat()}
            save_log(log)
            continue

        html_file = SITES / f"{key}.html"
        html_file.write_text(html, encoding="utf-8")
        print(f"  ✓ Site saved: {html_file.name}")

        # 2. Screenshot
        print(f"  → Taking screenshot...")
        png_file = SHOTS / f"{key}.png"
        ok = take_screenshot(html_file, png_file)
        if not ok:
            log[key] = {"status": "error", "stage": "screenshot", "ts": datetime.now().isoformat()}
            save_log(log)
            continue
        print(f"  ✓ Screenshot saved: {png_file.name}")

        # 3. Gmail draft
        if skip_gmail:
            print(f"  ⏭ Gmail skipped (--no-gmail flag)")
            log[key] = {"status": "site_only", "html": str(html_file), "png": str(png_file), "ts": datetime.now().isoformat()}
        else:
            print(f"  → Creating Gmail draft...")
            try:
                draft_id = create_draft(lead, str(png_file), YOUR_NAME, YOUR_EMAIL, YOUR_WEBSITE)
                print(f"  ✓ Draft created: {draft_id}")
                log[key] = {
                    "status":   "draft_created",
                    "draft_id": draft_id,
                    "html":     str(html_file),
                    "png":      str(png_file),
                    "lead":     lead,
                    "ts":       datetime.now().isoformat(),
                }
            except Exception as e:
                print(f"  ✗ Gmail draft failed: {e}")
                log[key] = {"status": "error", "stage": "gmail", "error": str(e), "ts": datetime.now().isoformat()}

        save_log(log)

        # Polite delay between API calls
        if i < len(leads):
            time.sleep(1.5)

    # ── Summary ───────────────────────────────────────────────────────────────
    done    = sum(1 for v in log.values() if v.get("status") == "draft_created")
    errors  = sum(1 for v in log.values() if v.get("status") == "error")
    print(f"\n{'─'*50}")
    print(f"✅ Done!  Drafts created: {done}  |  Errors: {errors}")
    print(f"📁 Sites:        {SITES}")
    print(f"📸 Screenshots:  {SHOTS}")
    print(f"📧 Check Gmail Drafts folder — review & send when ready!\n")


# ── CLI ───────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Outreach Automation Pipeline")
    parser.add_argument("--leads",    default="leads_example.xlsx", help="Path to leads Excel file")
    parser.add_argument("--limit",    type=int, default=10,         help="Max leads to process (default 10)")
    parser.add_argument("--no-gmail", action="store_true",          help="Skip Gmail draft creation (site + screenshot only)")
    args = parser.parse_args()

    required = ["ANTHROPIC_API_KEY"]
    if not args.no_gmail:
        required += ["YOUR_EMAIL"]
    missing = [k for k in required if not os.environ.get(k)]
    if missing:
        print(f"❌ Missing environment variables: {', '.join(missing)}")
        print("   Set them in .env or export them before running.")
        sys.exit(1)

    run(args.leads, args.limit, skip_gmail=args.no_gmail)
