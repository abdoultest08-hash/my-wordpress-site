"""STEP 1 — clean, validate, dedupe.

Reads the raw lead list (.xlsx or .csv) and writes:
  clean_leads.csv        rows that pass validation, one per company domain
  needs_review.csv       rejected rows + the reason(s) they were held back
  domain_duplicates.csv  extra contacts at a domain already represented
  email_duplicates.csv   exact duplicate email addresses

Nothing is ever silently discarded: every input row lands in exactly one of
those four files.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
from typing import Dict, List, Optional, Tuple

from common import (
    classify_email, clean_company, clean_person_name, company_key, host_of,
    is_junk, is_non_website_host, log, normalize_email, normalize_phone_uk,
    normalize_url, pretty_phone, read_table, registrable_domain,
    seniority_score, split_full_name, write_csv,
)

# Email-verifier verdicts that are unsafe to send to. Values are lowercased
# before lookup, so this covers MillionVerifier, ZeroBounce and NeverBounce.
BAD_VERIFY_STATUSES = {
    "bad", "invalid", "undeliverable", "do_not_mail", "do not mail",
    "bounced", "hard_bounce", "abuse", "spamtrap", "disposable",
}
RISKY_VERIFY_STATUSES = {
    "risky", "catch_all", "catch-all", "catchall", "accept_all", "accept-all",
    "unknown", "greylisted", "unverifiable",
}

# Service specialisms worth naming in a cold email, most specific first.
SERVICE_VOCAB: List[Tuple[str, re.Pattern]] = [
    ("live-in care", re.compile(r"\blive[- ]in care\b", re.I)),
    ("dementia care", re.compile(r"\bdementia\b", re.I)),
    ("end-of-life care", re.compile(r"\b(end of life|palliative)\b", re.I)),
    ("complex care", re.compile(r"\bcomplex (care|needs)\b", re.I)),
    ("supported living", re.compile(r"\bsupported living\b", re.I)),
    ("learning disability support", re.compile(r"\blearning disab", re.I)),
    ("autism support", re.compile(r"\bautism\b", re.I)),
    ("mental health support", re.compile(r"\bmental health\b", re.I)),
    ("respite care", re.compile(r"\brespite\b", re.I)),
    ("reablement", re.compile(r"\breablement\b", re.I)),
    ("night care", re.compile(r"\b(night care|waking nights?)\b", re.I)),
    ("children's services", re.compile(r"\bchildren'?s (services|care)\b", re.I)),
    ("domiciliary care", re.compile(r"\b(domiciliary|home ?care|care at home)\b", re.I)),
    ("personal care", re.compile(r"\bpersonal care\b", re.I)),
]

# Signals that a row is not a domiciliary/home-care provider. Advisory only —
# these set a flag for review, they never drop a lead on their own.
OFF_ICP_PATTERNS = re.compile(
    r"\b(architect|architecture|software|recruitment agency|law firm|"
    r"solicitor|accountancy|insurance broker|university|council)\b", re.I)

CLEAN_COLUMNS = [
    "lead_id", "first_name", "last_name", "full_name", "job_title",
    "seniority", "email", "email_domain", "email_type", "email_status",
    "phone", "phone_pretty", "company", "website", "domain", "linkedin",
    "service_focus", "flags", "source_row",
]

REVIEW_COLUMNS = [
    "review_reasons", "first_name", "last_name", "full_name", "job_title",
    "email", "phone", "company", "website", "email_status", "source_row",
]


def make_lead_id(email: str, website: str, row_number: int) -> str:
    seed = email or website or f"row-{row_number}"
    return "ld_" + hashlib.sha1(seed.encode("utf-8")).hexdigest()[:12]


def extract_service_focus(keywords: str, limit: int = 3) -> str:
    found: List[str] = []
    for label, pattern in SERVICE_VOCAB:
        if pattern.search(keywords or "") and label not in found:
            found.append(label)
        if len(found) >= limit:
            break
    return "; ".join(found)


def normalize_job_title(value: str) -> str:
    v = re.sub(r"\s+", " ", (value or "").strip(" ,;-"))
    if not v or is_junk(v):
        return ""
    if v.isupper() or v.islower():
        small = {"of", "and", "the", "for", "at", "in", "to", "&"}
        words = v.split(" ")
        v = " ".join(
            w.upper() if w.lower() in {"hr", "ceo", "coo", "cfo", "md", "it", "uk"}
            else (w.lower() if i and w.lower() in small else w.capitalize())
            for i, w in enumerate(words))
    return v


def normalize_row(row: dict) -> dict:
    """Turn one raw row into a canonical lead record plus its problem list."""
    reasons: List[str] = []
    flags: List[str] = []

    first = clean_person_name(row.get("first_name", ""))
    last = clean_person_name(row.get("last_name", ""))
    full = clean_person_name(row.get("full_name", ""))
    if not first and full:
        first, last = split_full_name(full)
    if not full:
        full = " ".join(p for p in (first, last) if p)
    if not first:
        reasons.append("missing_first_name")

    job_title = normalize_job_title(row.get("job_title", ""))

    email = normalize_email(row.get("email", ""))
    email_ok, email_problems, email_attrs = classify_email(email)
    reasons.extend(email_problems)

    email_status = (row.get("email_status") or "").strip().lower()
    if email_status in BAD_VERIFY_STATUSES:
        reasons.append(f"verifier_says_{email_status}")
    elif email_status in RISKY_VERIFY_STATUSES:
        flags.append(f"verifier_{email_status}")

    phone_e164, phone_problem = normalize_phone_uk(row.get("phone", ""))
    if phone_problem:
        flags.append(phone_problem)

    company = clean_company(row.get("company", ""))
    if not company:
        reasons.append("missing_company")

    website, website_problem = normalize_url(row.get("website", ""))
    if website_problem:
        reasons.append(website_problem)
    elif is_non_website_host(website):
        # A Facebook/CQC/directory listing is not the agency's own site. That
        # is itself a strong Website Offer signal, so keep the lead and let
        # STEP 2 record it rather than sending it to review.
        flags.append("no_own_website_directory_or_social_only")

    domain = registrable_domain(website) if website else ""

    if email and domain and email_attrs.get("email_domain"):
        if registrable_domain(email_attrs["email_domain"]) != domain:
            flags.append("email_domain_differs_from_website")

    keywords = row.get("keywords", "")
    if OFF_ICP_PATTERNS.search(company) or OFF_ICP_PATTERNS.search(keywords[:400]):
        flags.append("possible_off_icp")

    return {
        "lead_id": make_lead_id(email, website, row.get("_row_number", 0)),
        "first_name": first,
        "last_name": last,
        "full_name": full,
        "job_title": job_title,
        "seniority": seniority_score(job_title),
        "email": email,
        "email_domain": email_attrs.get("email_domain", ""),
        "email_type": email_attrs.get("email_type", ""),
        "email_status": email_status,
        "phone": phone_e164,
        "phone_pretty": pretty_phone(phone_e164),
        "company": company,
        "website": website,
        "domain": domain,
        "linkedin": (row.get("linkedin") or "").strip(),
        "service_focus": extract_service_focus(keywords),
        "flags": "; ".join(flags),
        "source_row": row.get("_row_number", ""),
        "_reasons": reasons,
        "_raw": row.get("_raw", {}),
    }


def dedupe_rank(lead: dict) -> tuple:
    """Higher sorts first — this is the contact we keep for a domain."""
    status_bonus = 2 if lead["email_status"] not in RISKY_VERIFY_STATUSES else 0
    type_bonus = {"personal": 2, "personal_freemail": 1, "role": 0}.get(
        lead["email_type"], 0)
    completeness = sum(1 for f in ("first_name", "last_name", "phone",
                                   "linkedin", "job_title") if lead.get(f))
    return (lead["seniority"], status_bonus, type_bonus, completeness,
            -int(lead["source_row"] or 0))


def clean(input_path: str,
          outdir: str,
          max_per_domain: int = 1,
          drop_risky: bool = False,
          sheet: Optional[str] = None,
          overrides: Optional[Dict[str, str]] = None) -> Dict[str, object]:
    rows, header_map = read_table(input_path, overrides, sheet)
    log(f"[clean] read {len(rows)} rows from {os.path.basename(input_path)}")
    log(f"[clean] mapped columns: {json.dumps(header_map, ensure_ascii=False)}")

    unmapped = set()
    for row in rows[:1]:
        unmapped = set(row.get("_raw", {}).keys()) - set(header_map.keys())
    if unmapped:
        log(f"[clean] unmapped columns (kept in needs_review only): {sorted(unmapped)}")

    leads: List[dict] = []
    review: List[dict] = []

    for row in rows:
        lead = normalize_row(row)
        if drop_risky and any(f.startswith("verifier_risky")
                              for f in lead["flags"].split("; ")):
            lead["_reasons"].append("risky_email_excluded_by_flag")
        if lead["_reasons"]:
            review.append({**lead, "review_reasons": "; ".join(lead["_reasons"])})
        else:
            leads.append(lead)

    # --- dedupe by email (exact) ---
    by_email: Dict[str, dict] = {}
    email_dupes: List[dict] = []
    for lead in sorted(leads, key=dedupe_rank, reverse=True):
        existing = by_email.get(lead["email"])
        if existing is None:
            by_email[lead["email"]] = lead
        else:
            email_dupes.append({**lead, "duplicate_of": existing["lead_id"]})
    deduped_email = list(by_email.values())

    # --- dedupe by domain (keep the most senior/complete contact) ---
    kept: List[dict] = []
    domain_dupes: List[dict] = []
    seen: Dict[str, List[dict]] = {}
    for lead in sorted(deduped_email, key=dedupe_rank, reverse=True):
        key = lead["domain"] or f"__nodomain__{lead['lead_id']}"
        bucket = seen.setdefault(key, [])
        if len(bucket) < max_per_domain:
            bucket.append(lead)
            kept.append(lead)
        else:
            domain_dupes.append({**lead, "duplicate_of": bucket[0]["lead_id"]})

    # Company-name collision across different domains (franchise branches,
    # rebrands). Flagged, not dropped — the domains are genuinely different.
    by_company: Dict[str, List[dict]] = {}
    for lead in kept:
        if lead["company"]:
            by_company.setdefault(company_key(lead["company"]), []).append(lead)
    for group in by_company.values():
        if len(group) > 1:
            for lead in group:
                extra = "same_company_name_different_domain"
                lead["flags"] = "; ".join(filter(None, [lead["flags"], extra]))

    kept.sort(key=lambda l: (l["company"].lower(), l["last_name"].lower()))

    os.makedirs(outdir, exist_ok=True)
    paths = {
        "clean": os.path.join(outdir, "clean_leads.csv"),
        "review": os.path.join(outdir, "needs_review.csv"),
        "email_dupes": os.path.join(outdir, "email_duplicates.csv"),
        "domain_dupes": os.path.join(outdir, "domain_duplicates.csv"),
    }
    write_csv(paths["clean"], kept, CLEAN_COLUMNS)
    write_csv(paths["review"], review, REVIEW_COLUMNS)
    write_csv(paths["email_dupes"], email_dupes, CLEAN_COLUMNS + ["duplicate_of"])
    write_csv(paths["domain_dupes"], domain_dupes, CLEAN_COLUMNS + ["duplicate_of"])

    stats = {
        "input_rows": len(rows),
        "passed_validation": len(leads),
        "needs_review": len(review),
        "email_duplicates_removed": len(email_dupes),
        "domain_duplicates_removed": len(domain_dupes),
        "clean_leads": len(kept),
        "paths": paths,
    }
    log(f"[clean] {stats['clean_leads']} clean | {stats['needs_review']} review | "
        f"{stats['email_duplicates_removed']} email dupes | "
        f"{stats['domain_duplicates_removed']} domain dupes")
    return stats


def main() -> None:
    ap = argparse.ArgumentParser(description="STEP 1 — clean and dedupe a lead list")
    ap.add_argument("--input", required=True, help=".xlsx or .csv lead list")
    ap.add_argument("--outdir", default="output")
    ap.add_argument("--sheet", default=None, help="worksheet name (xlsx only)")
    ap.add_argument("--max-per-domain", type=int, default=1,
                    help="contacts to keep per company domain (default 1)")
    ap.add_argument("--drop-risky", action="store_true",
                    help="send verifier 'risky'/catch-all emails to needs_review")
    ap.add_argument("--map", default=None,
                    help='JSON override, e.g. \'{"email":"Work Email"}\'')
    args = ap.parse_args()

    overrides = json.loads(args.map) if args.map else None
    clean(args.input, args.outdir, args.max_per_domain, args.drop_risky,
          args.sheet, overrides)


if __name__ == "__main__":
    main()
