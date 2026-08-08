"""STEP 3 — join site results onto leads and split into the two offers.

Segment A ("Website Offer")        website_quality == bad
Segment B ("HR Automation Offer")  website_quality == ok or good

Both files are shaped for a direct Instantly CSV import: every column becomes
a {{variable}} you can use in the sequence.
"""

from __future__ import annotations

import argparse
import csv
import os
import re
from typing import Dict, List

from common import log, write_csv

# Instantly maps CSV headers straight to variables, so these names are what
# you type in the sequence editor as {{personalization_notes}} etc.
INSTANTLY_COLUMNS = [
    "first_name", "last_name", "email", "company", "website",
    "personalization_notes",
    # --- supporting custom fields ---
    "phone", "job_title", "service_focus", "website_quality", "website_score",
    "website_reasons", "website_issue", "email_status", "email_type",
    "linkedin", "domain", "lead_id",
]

# Sales-relevance order: whichever of these matches first becomes the hook.
ISSUE_PRIORITY = [
    "not mobile responsive",
    "width=device-width",
    "no HTTPS",
    "broken SSL",
    "parked",
    "does not resolve",
    "timed out",
    "unreachable",
    "server error",
    "returns HTTP",
    "low-end site builder",
    "legacy platform",
    "old default theme",
    "plain unstyled HTML",
    "1990s-era markup",
    "outdated WordPress",
    "copyright still says",
    "very slow homepage",
    "slow homepage",
    "single-page site",
    "thin homepage",
    "almost no page content",
    "no images",
    "sluggish homepage",
    "no responsive CSS",
    "no copyright year",
    "SSL certificate expires",
]

POSITIVE_REASON = "modern, secure, mobile-friendly"


def rank_issues(reasons: str) -> List[str]:
    """Order a site's reasons so the most pitchable problem comes first."""
    items = [r.strip() for r in (reasons or "").split(";") if r.strip()]
    items = [r for r in items if POSITIVE_REASON not in r]

    def key(reason: str) -> int:
        for i, marker in enumerate(ISSUE_PRIORITY):
            if marker.lower() in reason.lower():
                return i
        return len(ISSUE_PRIORITY)

    return sorted(items, key=key)


def _lower_first(text: str) -> str:
    return text[:1].lower() + text[1:] if text else text


def notes_for_website_offer(lead: dict, issues: List[str]) -> str:
    domain = lead.get("domain") or lead.get("website", "")
    parts: List[str] = []
    if issues:
        head = _lower_first(issues[0])
        if len(issues) > 1:
            parts.append(f"{domain} — {head}, and {_lower_first(issues[1])}")
        else:
            parts.append(f"{domain} — {head}")
    else:
        parts.append(f"{domain} — site scores "
                     f"{lead.get('website_score', '?')}/100 on basic web health")
    if lead.get("service_focus"):
        parts.append(f"Specialises in {lead['service_focus']}")
    return ". ".join(parts) + "."


def notes_for_hr_offer(lead: dict) -> str:
    company = lead.get("company") or "the agency"
    quality = lead.get("website_quality", "ok")
    score = lead.get("website_score", "")
    parts = [f"Site is {quality} ({score}/100) — do NOT pitch web design"]
    if lead.get("service_focus"):
        parts.append(f"{company} runs {lead['service_focus']}")
    parts.append("Angle: carer onboarding, DBS/right-to-work checks, "
                 "training renewals and CQC evidence trails")
    return ". ".join(parts) + "."


def segment(leads_path: str, checks_path: str, outdir: str) -> Dict[str, object]:
    with open(leads_path, "r", encoding="utf-8-sig", newline="") as fh:
        leads = list(csv.DictReader(fh))
    with open(checks_path, "r", encoding="utf-8-sig", newline="") as fh:
        checks = {row["lead_id"]: row for row in csv.DictReader(fh)}

    segment_a: List[dict] = []
    segment_b: List[dict] = []
    unchecked: List[dict] = []

    for lead in leads:
        check = checks.get(lead["lead_id"])
        if not check:
            unchecked.append(lead)
            continue

        row = dict(lead)
        row["website"] = check.get("final_url") or lead.get("website", "")
        row["website_quality"] = check.get("website_quality", "")
        row["website_score"] = check.get("website_score", "")
        row["website_reasons"] = check.get("website_reasons", "")

        issues = rank_issues(row["website_reasons"])
        row["website_issue"] = issues[0] if issues else ""

        if row["website_quality"] == "bad":
            row["personalization_notes"] = notes_for_website_offer(row, issues)
            segment_a.append(row)
        else:
            row["personalization_notes"] = notes_for_hr_offer(row)
            segment_b.append(row)

    for bucket in (segment_a, segment_b):
        bucket.sort(key=lambda r: (int(r.get("website_score") or 0),
                                   r.get("company", "").lower()))

    os.makedirs(outdir, exist_ok=True)
    paths = {
        "segment_a": os.path.join(outdir, "segment_a_website_offer.csv"),
        "segment_b": os.path.join(outdir, "segment_b_hr_automation_offer.csv"),
    }
    write_csv(paths["segment_a"], segment_a, INSTANTLY_COLUMNS)
    write_csv(paths["segment_b"], segment_b, INSTANTLY_COLUMNS)

    if unchecked:
        paths["unchecked"] = os.path.join(outdir, "unchecked_leads.csv")
        write_csv(paths["unchecked"], unchecked, list(leads[0].keys()))

    log(f"[segment] A (website offer)={len(segment_a)}  "
        f"B (hr automation)={len(segment_b)}  unchecked={len(unchecked)}")
    return {
        "segment_a": len(segment_a),
        "segment_b": len(segment_b),
        "unchecked": len(unchecked),
        "paths": paths,
    }


def main() -> None:
    ap = argparse.ArgumentParser(description="STEP 3 — segment into two offers")
    ap.add_argument("--leads", default="output/clean_leads.csv")
    ap.add_argument("--checks", default="output/site_checks.csv")
    ap.add_argument("--outdir", default="output")
    args = ap.parse_args()
    segment(args.leads, args.checks, args.outdir)


if __name__ == "__main__":
    main()
