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

# --------------------------------------------------------------------------
# Franchise detection
# --------------------------------------------------------------------------
# Franchise networks (Home Instead, Bluebird Care, Radfield) put dozens of
# independently-owned businesses behind one head-office domain. Plain domain
# dedupe would collapse them into a single lead and throw away real prospects,
# while a large care group (one company, many employees) genuinely should
# collapse to one. These rules tell the two cases apart.

FRANCHISE_MIN_CONTACTS = 4
OWNER_TITLE_RE = re.compile(r"\b(owner|founder|proprietor|franchisee)\b", re.I)

# Words that describe the sector rather than a specific branch, so "Cygnet"
# vs "Cygnet Health Care" reads as one company while "Bluebird Care Carlisle"
# reads as a branch.
GENERIC_BRAND_TOKENS = {
    "care", "cares", "caring", "carers", "health", "healthcare", "home",
    "homes", "homecare", "senior", "seniors", "living", "support", "supported",
    "services", "service", "group", "holdings", "uk", "ltd", "limited", "llp",
    "plc", "cic", "nursing", "medical", "solutions", "agency", "agencies",
    "people", "team", "national", "the", "and", "of", "at", "for", "in",
    "company", "co", "domiciliary", "community", "quality", "professional",
}

TITLE_BRANCH_PATTERNS = [
    re.compile(r"\b([A-Z][\w'&-]*(?:\s+[A-Z][\w'&-]*)*)\s+franchise\b"),
    re.compile(r"\bfranchise\s+(?:owner\s+)?(?:for|in|at)\s+"
               r"([A-Z][\w'&-]*(?:\s+[A-Z][\w'&-]*)*)"),
    re.compile(r"\bat\s+([A-Z][\w'&-]*(?:\s+[A-Z][\w'&-]*)*)"),
    re.compile(r",\s*([A-Z][\w'&-]*(?:\s+[A-Z][\w'&-]*)*)\s*$"),
]


def _tokens(text: str) -> List[str]:
    return [t for t in re.split(r"[^A-Za-z0-9'&]+", (text or "").lower()) if t]


def _strip_generic(tokens: List[str]) -> List[str]:
    return [t for t in tokens if t not in GENERIC_BRAND_TOKENS and not t.isdigit()]


def _branch_from_title(job_title: str) -> str:
    for pattern in TITLE_BRANCH_PATTERNS:
        match = pattern.search(job_title or "")
        if match:
            label = " ".join(_strip_generic(_tokens(match.group(1))))
            if label:
                return label
    return ""


def detect_franchise(group: List[dict]) -> Tuple[bool, Dict[str, str]]:
    """Decide whether a shared domain is a franchise network.

    Returns (is_franchise, {lead_id: branch_label}). A branch label is the part
    of a company name or job title that identifies a specific branch once the
    shared brand and generic sector words are removed.
    """
    labels: Dict[str, str] = {}
    if len(group) < FRANCHISE_MIN_CONTACTS:
        return False, labels

    name_tokens = [_tokens(l["company"]) for l in group]
    shortest = min((len(t) for t in name_tokens if t), default=0)
    prefix_len = 0
    for i in range(shortest):
        column = {t[i] for t in name_tokens if len(t) > i}
        if len(column) == 1:
            prefix_len += 1
        else:
            break

    for lead, tokens in zip(group, name_tokens):
        label = " ".join(_strip_generic(tokens[prefix_len:]))
        if not label:
            label = _branch_from_title(lead["job_title"])
        labels[lead["lead_id"]] = label

    distinct = {v for v in labels.values() if v}
    owners = sum(1 for l in group if OWNER_TITLE_RE.search(l["job_title"] or ""))

    # Two thresholds, both deliberately conservative. A single odd label is
    # usually just a spelling variant of the same company ("Nurse Plus UK" vs
    # "Nurseplus UK"), so branch names alone need three of them. Two or more
    # owner-titled contacts is strong evidence on its own: one company has one
    # owner, a franchise network has many.
    is_franchise = len(distinct) >= 3 or owners >= 2
    return is_franchise, labels

CLEAN_COLUMNS = [
    "lead_id", "first_name", "last_name", "full_name", "job_title",
    "seniority", "email", "email_domain", "email_type", "email_status",
    "phone", "phone_pretty", "company", "website", "domain", "linkedin",
    "service_focus", "is_franchise_branch", "franchise_branch", "flags",
    "source_row",
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
          overrides: Optional[Dict[str, str]] = None,
          keep_franchise_branches: bool = True) -> Dict[str, object]:
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
    groups: Dict[str, List[dict]] = {}
    for lead in sorted(deduped_email, key=dedupe_rank, reverse=True):
        key = lead["domain"] or f"__nodomain__{lead['lead_id']}"
        groups.setdefault(key, []).append(lead)

    kept: List[dict] = []
    domain_dupes: List[dict] = []
    franchise_networks = 0

    for group in groups.values():
        for lead in group:
            lead["is_franchise_branch"] = "no"
            lead["franchise_branch"] = ""

        primary = group[:max_per_domain]
        rest = group[max_per_domain:]
        kept.extend(primary)

        is_franchise, labels = (False, {})
        if keep_franchise_branches and rest:
            is_franchise, labels = detect_franchise(group)

        if not is_franchise:
            for lead in rest:
                domain_dupes.append({**lead, "duplicate_of": primary[0]["lead_id"]})
            continue

        franchise_networks += 1
        # One lead per distinct branch, plus every owner-titled contact: in a
        # franchise each of those is a separate business, not a colleague.
        seen_labels = {labels.get(l["lead_id"], "") for l in primary}
        for lead in rest:
            label = labels.get(lead["lead_id"], "")
            is_owner = bool(OWNER_TITLE_RE.search(lead["job_title"] or ""))
            new_branch = bool(label) and label not in seen_labels
            if new_branch or is_owner:
                if label:
                    seen_labels.add(label)
                lead["is_franchise_branch"] = "yes"
                lead["franchise_branch"] = label
                lead["flags"] = "; ".join(filter(
                    None, [lead["flags"], "franchise_branch_of_" + lead["domain"]]))
                kept.append(lead)
            else:
                domain_dupes.append({**lead, "duplicate_of": primary[0]["lead_id"]})

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

    franchise_kept = sum(1 for l in kept if l.get("is_franchise_branch") == "yes")
    stats = {
        "franchise_networks": franchise_networks,
        "franchise_branches_kept": franchise_kept,
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
        f"{stats['domain_duplicates_removed']} domain dupes | "
        f"{franchise_kept} franchise branches across "
        f"{franchise_networks} networks")
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
    ap.add_argument("--no-franchise-branches", action="store_true",
                    help="collapse franchise networks to one contact per domain")
    ap.add_argument("--map", default=None,
                    help='JSON override, e.g. \'{"email":"Work Email"}\'')
    args = ap.parse_args()

    overrides = json.loads(args.map) if args.map else None
    clean(args.input, args.outdir, args.max_per_domain, args.drop_risky,
          args.sheet, overrides, not args.no_franchise_branches)


if __name__ == "__main__":
    main()
