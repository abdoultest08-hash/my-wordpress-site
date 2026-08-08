"""Shared helpers: CSV I/O, header mapping, and field normalization.

Stdlib only — including the .xlsx reader — so the whole pipeline runs anywhere
Python 3.9+ does, with nothing to install.
"""

from __future__ import annotations

import csv
import io
import re
import sys
from typing import Dict, Iterable, List, Optional, Tuple
from urllib.parse import urlsplit, urlunsplit

# --------------------------------------------------------------------------
# Header mapping
# --------------------------------------------------------------------------
# Lead exports come from Apollo / Sales Navigator / scrapers / hand-built
# sheets, each with its own column names. Everything downstream works off the
# canonical names on the left, so a new list only needs aliases added here
# (or a --map override on the CLI).

HEADER_ALIASES: Dict[str, Tuple[str, ...]] = {
    "first_name": (
        "first_name", "firstname", "first", "fname", "given_name",
        "contact_first_name", "first name",
    ),
    "last_name": (
        "last_name", "lastname", "last", "lname", "surname", "family_name",
        "contact_last_name", "last name",
    ),
    "full_name": (
        "full_name", "fullname", "name", "contact_name", "contact",
        "person", "full name",
    ),
    "email": (
        "email", "email_address", "e-mail", "emailaddress", "work_email",
        "primary_email", "email 1", "email1", "email address",
    ),
    "phone": (
        "phone", "phone_number", "telephone", "tel", "mobile", "contact_number",
        "direct_phone", "company_phone", "phone number",
        "company_phone_number", "business_phone", "work_phone",
        "direct_phone_number", "work_direct_phone", "mobile_number",
    ),
    "company": (
        "company", "company_name", "organisation", "organization", "org",
        "business", "business_name", "account", "agency", "agency_name",
        "company name", "cleaned_company_name", "clean_company_name",
    ),
    "website": (
        "website", "website_url", "url", "site", "web", "domain",
        "company_website", "web_address", "homepage", "website url",
        "company_website_short", "website_short", "company_domain",
    ),
    "job_title": (
        "job_title", "title", "role", "position", "jobtitle", "designation",
        "job title",
    ),
    "email_status": (
        "email_status", "millionverifier_status", "million_verifier_status",
        "verification_status", "email_verification", "mv_status",
        "zerobounce_status", "neverbounce_result", "bounce_status",
    ),
    "keywords": (
        "keywords", "company_keywords", "tags", "industry_keywords",
        "technologies", "specialties",
    ),
    "city": ("city", "town", "locality"),
    "region": ("region", "county", "state", "province", "area"),
    "postcode": ("postcode", "post_code", "zip", "zip_code", "postal_code"),
    "linkedin": (
        "linkedin", "linkedin_url", "linkedin_profile", "li_url",
        "linkedin_link", "linkedin_profile_url", "person_linkedin_url",
    ),
}


def _norm_header(h: str) -> str:
    """Lowercase, strip BOM/punctuation so 'First Name*' == 'first_name'."""
    h = (h or "").replace("﻿", "").strip().lower()
    h = re.sub(r"[^a-z0-9]+", "_", h).strip("_")
    return h


def build_header_map(fieldnames: Iterable[str],
                     overrides: Optional[Dict[str, str]] = None) -> Dict[str, str]:
    """Map raw CSV header -> canonical field name.

    Returns only the columns we recognize; unrecognized columns are preserved
    verbatim by read_csv so nothing from the source list is ever lost.
    """
    overrides = overrides or {}
    # Overrides are given as {canonical: raw_header}; invert for lookup.
    inverted = {_norm_header(raw): canon for canon, raw in overrides.items()}

    lookup: Dict[str, str] = {}
    for canon, aliases in HEADER_ALIASES.items():
        for alias in aliases:
            lookup[_norm_header(alias)] = canon

    mapping: Dict[str, str] = {}
    for raw in fieldnames or []:
        key = _norm_header(raw)
        if key in inverted:
            mapping[raw] = inverted[key]
        elif key in lookup:
            mapping[raw] = lookup[key]
    return mapping


# --------------------------------------------------------------------------
# CSV I/O
# --------------------------------------------------------------------------

def read_csv(path: str,
             overrides: Optional[Dict[str, str]] = None) -> Tuple[List[dict], Dict[str, str]]:
    """Read a lead CSV into canonical dicts.

    Each row keeps its original columns under ``_raw`` so needs_review.csv can
    hand back everything the source file had.
    """
    with open(path, "r", encoding="utf-8-sig", errors="replace", newline="") as fh:
        sample = fh.read(64 * 1024)
        fh.seek(0)
        try:
            dialect = csv.Sniffer().sniff(sample, delimiters=",;\t|")
        except csv.Error:
            dialect = csv.get_dialect("excel")
        reader = csv.DictReader(fh, dialect=dialect)
        header_map = build_header_map(reader.fieldnames or [], overrides)

        rows: List[dict] = []
        for i, raw_row in enumerate(reader, start=2):  # row 1 is the header
            row: dict = {"_row_number": i, "_raw": dict(raw_row)}
            for raw_key, canon in header_map.items():
                value = raw_row.get(raw_key)
                # Don't let a blank duplicate column clobber a filled one.
                if value and value.strip() and not row.get(canon):
                    row[canon] = value.strip()
            rows.append(row)
    return rows, header_map


_XL_MAIN = "{http://schemas.openxmlformats.org/spreadsheetml/2006/main}"
_XL_REL = "{http://schemas.openxmlformats.org/officeDocument/2006/relationships}"


def _column_index(ref: str) -> int:
    """'A' -> 0, 'B' -> 1, 'AA' -> 26."""
    index = 0
    for char in ref:
        if not char.isalpha():
            break
        index = index * 26 + (ord(char.upper()) - 64)
    return index - 1


def _text_of(node) -> str:
    return "".join(t.text or "" for t in node.iter() if t.tag == _XL_MAIN + "t")


def _read_xlsx_stdlib(path: str, sheet: Optional[str] = None) -> List[List[str]]:
    """Minimal .xlsx reader (zip + XML), so no dependency is required.

    Handles shared strings, inline strings and plain values — everything a lead
    export contains. Formulas resolve to their cached value, same as openpyxl's
    data_only mode.
    """
    import xml.etree.ElementTree as ET
    import zipfile

    with zipfile.ZipFile(path) as archive:
        names = set(archive.namelist())

        shared: List[str] = []
        if "xl/sharedStrings.xml" in names:
            for item in ET.fromstring(archive.read("xl/sharedStrings.xml")):
                shared.append(_text_of(item))

        targets: List[Tuple[str, str]] = []
        if "xl/workbook.xml" in names and "xl/_rels/workbook.xml.rels" in names:
            rels = {r.get("Id"): r.get("Target")
                    for r in ET.fromstring(archive.read("xl/_rels/workbook.xml.rels"))}
            for node in ET.fromstring(archive.read("xl/workbook.xml")).iter():
                if node.tag == _XL_MAIN + "sheet":
                    targets.append((node.get("name") or "",
                                    rels.get(node.get(_XL_REL + "id"), "")))

        target = ""
        if sheet:
            for name, rel in targets:
                if name == sheet:
                    target = rel
                    break
            if not target:
                raise SystemExit(
                    f"Worksheet {sheet!r} not found. Available: "
                    f"{[n for n, _ in targets]}")
        elif targets:
            target = targets[0][1]

        if target:
            target = target.lstrip("/")
            if not target.startswith("xl/"):
                target = "xl/" + target
        if target not in names:
            candidates = sorted(n for n in names
                                if n.startswith("xl/worksheets/") and
                                n.endswith(".xml"))
            if not candidates:
                raise SystemExit(f"No worksheet found inside {path}")
            target = candidates[0]

        grid: List[List[str]] = []
        for row in ET.fromstring(archive.read(target)).iter(_XL_MAIN + "row"):
            cells: Dict[int, str] = {}
            for position, cell in enumerate(row.findall(_XL_MAIN + "c")):
                ref = cell.get("r") or ""
                index = _column_index(ref) if ref and ref[0].isalpha() else position
                kind = cell.get("t")
                value_node = cell.find(_XL_MAIN + "v")
                if kind == "s" and value_node is not None:
                    try:
                        value = shared[int(value_node.text or "0")]
                    except (ValueError, IndexError):
                        value = ""
                elif kind == "inlineStr":
                    inline = cell.find(_XL_MAIN + "is")
                    value = _text_of(inline) if inline is not None else ""
                elif value_node is not None:
                    value = value_node.text or ""
                else:
                    value = ""
                if index >= 0:
                    cells[index] = value.strip()
            width = max(cells) + 1 if cells else 0
            grid.append([cells.get(i, "") for i in range(width)])
        return grid


def read_xlsx(path: str,
              overrides: Optional[Dict[str, str]] = None,
              sheet: Optional[str] = None) -> Tuple[List[dict], Dict[str, str]]:
    """Read the first (or named) worksheet.

    Uses openpyxl when it is installed and falls back to the built-in reader
    above otherwise, so .xlsx input works on a stock Python.
    """
    def cell(v) -> str:
        if v is None:
            return ""
        if isinstance(v, float) and v.is_integer():
            return str(int(v))       # stop phone/postcode turning into 1.234e+10
        return str(v).strip()

    try:
        import openpyxl
    except ImportError:
        grid = [[cell(v) for v in row] for row in _read_xlsx_stdlib(path, sheet)]
    else:
        wb = openpyxl.load_workbook(path, data_only=True)
        ws = wb[sheet] if sheet else wb.worksheets[0]
        grid = [[cell(v) for v in row] for row in ws.iter_rows(values_only=True)]

    if not grid:
        return [], {}

    headers = [cell(h) for h in grid[0]]
    header_map = build_header_map(headers, overrides)

    rows: List[dict] = []
    for i, values in enumerate(grid[1:], start=2):
        raw = {headers[j]: v for j, v in enumerate(values) if j < len(headers)}
        if not any(raw.values()):
            continue                 # trailing blank rows Excel loves to keep
        row: dict = {"_row_number": i, "_raw": raw}
        for raw_key, canon in header_map.items():
            value = raw.get(raw_key)
            if value and not row.get(canon):
                row[canon] = value
        rows.append(row)
    return rows, header_map


def read_table(path: str,
               overrides: Optional[Dict[str, str]] = None,
               sheet: Optional[str] = None) -> Tuple[List[dict], Dict[str, str]]:
    """Read .csv/.tsv/.txt or .xlsx/.xlsm based on the file extension."""
    lower = path.lower()
    if lower.endswith((".xlsx", ".xlsm")):
        return read_xlsx(path, overrides, sheet)
    if lower.endswith(".xls"):
        raise SystemExit("Legacy .xls is not supported — re-save as .xlsx or .csv.")
    return read_csv(path, overrides)


def write_csv(path: str, rows: List[dict], columns: List[str]) -> None:
    with open(path, "w", encoding="utf-8", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=columns, extrasaction="ignore")
        writer.writeheader()
        for row in rows:
            writer.writerow({c: row.get(c, "") for c in columns})


def preview_csv(path: str, limit: int = 10, max_col_width: int = 34) -> str:
    """Render the first `limit` data rows as a text table for sanity-checking."""
    with open(path, "r", encoding="utf-8-sig", newline="") as fh:
        reader = csv.reader(fh)
        rows = []
        for i, row in enumerate(reader):
            if i > limit:
                break
            rows.append(row)
    if not rows:
        return "(empty file)"

    def clip(v: str) -> str:
        v = (v or "").replace("\n", " ").replace("\r", " ")
        return v if len(v) <= max_col_width else v[: max_col_width - 1] + "…"

    rows = [[clip(c) for c in r] for r in rows]
    widths = [max(len(r[i]) if i < len(r) else 0 for r in rows)
              for i in range(len(rows[0]))]

    out = io.StringIO()
    for idx, row in enumerate(rows):
        cells = [(row[i] if i < len(row) else "").ljust(widths[i])
                 for i in range(len(widths))]
        out.write(" | ".join(cells).rstrip() + "\n")
        if idx == 0:
            out.write("-+-".join("-" * w for w in widths) + "\n")
    return out.getvalue()


# --------------------------------------------------------------------------
# Name / company normalization
# --------------------------------------------------------------------------

_JUNK_TOKENS = {
    "", "n/a", "na", "none", "null", "nil", "-", "--", ".", "unknown",
    "test", "tbc", "tbd", "xxx", "xxxx", "no", "not available", "#n/a",
    "#value!", "0",
}

_TITLE_PREFIXES = {"mr", "mrs", "ms", "miss", "dr", "prof", "sir", "mx"}
_NAME_SUFFIXES = {"jr", "sr", "ii", "iii", "iv", "phd", "mba", "rn", "msc", "bsc"}


def is_junk(value: Optional[str]) -> bool:
    return (value or "").strip().lower() in _JUNK_TOKENS


def clean_person_name(value: Optional[str]) -> str:
    """Strip titles/credentials and normalize capitalization."""
    v = (value or "").strip()
    if is_junk(v):
        return ""
    v = re.sub(r"[,;]+", " ", v)
    v = re.sub(r"\s+", " ", v).strip()
    parts = [p for p in v.split(" ") if p]
    kept = []
    for p in parts:
        bare = p.strip(".").lower()
        if bare in _TITLE_PREFIXES or bare in _NAME_SUFFIXES:
            continue
        kept.append(p)
    if not kept:
        return ""
    out = []
    for p in kept:
        if re.search(r"[A-Z]", p[1:]) and p.upper() != p:
            out.append(p)          # McDonald, O'Brien-Smith already cased
        elif "'" in p or "-" in p:
            out.append(re.sub(r"(^|['-])([a-z])",
                              lambda m: m.group(1) + m.group(2).upper(), p.lower()))
        else:
            out.append(p.capitalize())
    return " ".join(out)


def split_full_name(full: str) -> Tuple[str, str]:
    parts = clean_person_name(full).split(" ")
    if not parts or not parts[0]:
        return "", ""
    if len(parts) == 1:
        return parts[0], ""
    return parts[0], " ".join(parts[1:])


_COMPANY_NOISE = re.compile(
    r"\s*\b(ltd\.?|limited|llp|plc|cic|c\.i\.c\.?|co\.?|company|"
    r"uk|t/a)\b\.?\s*$", re.I)


def clean_company(value: Optional[str]) -> str:
    """Tidy whitespace/casing; keep the legal suffix (agencies use it in their branding)."""
    v = re.sub(r"\s+", " ", (value or "").strip())
    if is_junk(v):
        return ""
    v = v.strip(" ,;-|")
    if v and (v.isupper() or v.islower()) and len(v) > 3:
        v = " ".join(w if w.upper() in {"UK", "CIC", "LLP", "PLC", "LTD", "NHS"}
                     else w.capitalize() for w in v.split(" "))
        v = re.sub(r"\bLtd\b", "Ltd", v)
    return v


def company_key(value: str) -> str:
    """Loose key for fuzzy same-company detection (suffixes and spacing removed)."""
    v = (value or "").lower()
    prev = None
    while v != prev:
        prev = v
        v = _COMPANY_NOISE.sub("", v).strip(" .,-&")
    return re.sub(r"[^a-z0-9]", "", v)


# --------------------------------------------------------------------------
# Email
# --------------------------------------------------------------------------

EMAIL_RE = re.compile(
    r"^[A-Za-z0-9!#$%&'*+/=?^_`{|}~-]+"
    r"(?:\.[A-Za-z0-9!#$%&'*+/=?^_`{|}~-]+)*"
    r"@(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+"
    r"[A-Za-z]{2,63}$"
)

PLACEHOLDER_EMAIL_DOMAINS = {
    "example.com", "example.org", "example.net", "test.com", "email.com",
    "domain.com", "yourdomain.com", "company.com", "website.com", "none.com",
    "noemail.com", "no-email.com", "sample.com", "mydomain.com",
}

DISPOSABLE_EMAIL_DOMAINS = {
    "mailinator.com", "guerrillamail.com", "10minutemail.com", "yopmail.com",
    "tempmail.com", "temp-mail.org", "trashmail.com", "throwawaymail.com",
    "sharklasers.com", "getnada.com", "maildrop.cc", "dispostable.com",
}

ROLE_LOCALPARTS = {
    "info", "admin", "enquiries", "enquiry", "hello", "contact", "office",
    "mail", "sales", "support", "help", "team", "reception", "accounts",
    "recruitment", "jobs", "careers", "hr", "referrals", "care", "general",
}

UNDELIVERABLE_LOCALPARTS = {
    "noreply", "no-reply", "donotreply", "do-not-reply", "postmaster",
    "abuse", "mailer-daemon", "bounce", "bounces", "unsubscribe",
}

FREE_EMAIL_DOMAINS = {
    "gmail.com", "googlemail.com", "yahoo.com", "yahoo.co.uk", "hotmail.com",
    "hotmail.co.uk", "outlook.com", "live.co.uk", "live.com", "aol.com",
    "icloud.com", "btinternet.com", "sky.com", "msn.com", "me.com",
    "protonmail.com", "proton.me", "talktalk.net", "virginmedia.com",
    "blueyonder.co.uk", "ntlworld.com", "mail.com", "gmx.com", "yandex.com",
}


def normalize_email(value: Optional[str]) -> str:
    """Lowercase, unwrap `Name <a@b.com>`, and take the first of a delimited list."""
    v = (value or "").strip()
    if is_junk(v):
        return ""
    m = re.search(r"<([^>]+)>", v)
    if m:
        v = m.group(1)
    v = re.split(r"[;,/\s]+", v.strip())[0] if v else v
    v = v.strip().strip("<>\"'").lower()
    v = re.sub(r"^mailto:", "", v)
    return v


def classify_email(email: str) -> Tuple[bool, List[str], Dict[str, str]]:
    """Return (is_valid, problems, attributes)."""
    problems: List[str] = []
    attrs: Dict[str, str] = {"email_domain": "", "email_type": ""}

    if not email:
        return False, ["missing_email"], attrs
    if not EMAIL_RE.match(email):
        return False, ["invalid_email_format"], attrs

    local, _, domain = email.partition("@")
    attrs["email_domain"] = domain

    if domain in PLACEHOLDER_EMAIL_DOMAINS:
        problems.append("placeholder_email_domain")
    if domain in DISPOSABLE_EMAIL_DOMAINS:
        problems.append("disposable_email_domain")
    if local in UNDELIVERABLE_LOCALPARTS:
        problems.append("unmonitored_mailbox")

    base_local = re.sub(r"[._-]?\d+$", "", local)
    if local in ROLE_LOCALPARTS or base_local in ROLE_LOCALPARTS:
        attrs["email_type"] = "role"
    elif domain in FREE_EMAIL_DOMAINS:
        attrs["email_type"] = "personal_freemail"
    else:
        attrs["email_type"] = "personal"

    return (not problems), problems, attrs


# --------------------------------------------------------------------------
# Phone (UK)
# --------------------------------------------------------------------------

def normalize_phone_uk(value: Optional[str]) -> Tuple[str, str]:
    """Return (E.164 phone, problem). Empty problem means it parsed cleanly."""
    v = (value or "").strip()
    if is_junk(v):
        return "", ""

    v = re.split(r"\b(?:ext|x|extension)\b\.?", v, flags=re.I)[0]
    has_plus = v.lstrip().startswith("+")
    digits = re.sub(r"\D", "", v)
    if not digits:
        return "", "invalid_phone"

    if digits.startswith("0044"):
        digits = digits[4:]
    elif digits.startswith("44") and (has_plus or len(digits) >= 12):
        digits = digits[2:]
    elif digits.startswith("0"):
        digits = digits[1:]

    if has_plus and not re.sub(r"\D", "", v).startswith(("44", "0044")):
        return "+" + re.sub(r"\D", "", v), ""   # non-UK international, keep as-is

    if len(digits) == 10 and digits[0] in "12357890":
        return "+44" + digits, ""
    if len(digits) == 9 and digits[0] in "12345789":
        return "+44" + digits, ""               # some 9-digit UK area codes
    return "+44" + digits if digits else "", "unverified_phone_format"


def pretty_phone(e164: str) -> str:
    """Human-readable UK formatting; Instantly is fine with either."""
    if not e164.startswith("+44"):
        return e164
    rest = e164[3:]
    if rest.startswith("7") and len(rest) == 10:
        return f"+44 {rest[:4]} {rest[4:]}"
    if rest.startswith(("20", "23", "24", "28", "29")) and len(rest) == 10:
        return f"+44 {rest[:2]} {rest[2:6]} {rest[6:]}"
    if len(rest) == 10:
        return f"+44 {rest[:3]} {rest[3:6]} {rest[6:]}"
    return e164


# --------------------------------------------------------------------------
# URLs / domains
# --------------------------------------------------------------------------

# Not the lead's own site: directory listings, regulators and social profiles.
NON_WEBSITE_HOSTS = {
    "facebook.com", "m.facebook.com", "fb.com", "instagram.com",
    "linkedin.com", "twitter.com", "x.com", "tiktok.com", "youtube.com",
    "cqc.org.uk", "nhs.uk", "carehome.co.uk", "homecare.co.uk",
    "yell.com", "yelp.com", "yell.co.uk", "trustpilot.com", "google.com",
    "sites.google.com", "business.site", "checkatrade.com", "thomsonlocal.com",
    "indeed.com", "indeed.co.uk", "companieshouse.gov.uk",
    "find-and-update.company-information.service.gov.uk",
}

PUBLIC_SUFFIX_TWO_LEVEL = {
    "co.uk", "org.uk", "ltd.uk", "plc.uk", "me.uk", "net.uk", "sch.uk",
    "ac.uk", "gov.uk", "nhs.uk", "com.au", "co.nz", "co.za", "com.br",
    # Private registries that resell subdomains: unrelated businesses live
    # under these, so they must not collapse into one "domain".
    "uk.com", "uk.net", "gb.com", "gb.net", "eu.com", "co.com", "org.es",
}


def normalize_url(value: Optional[str]) -> Tuple[str, str]:
    """Return (canonical url, problem).

    Forces https://, lowercases the host, drops trailing slashes/fragments and
    common tracking params. Path case is preserved (paths can be case sensitive).
    """
    v = (value or "").strip().strip("<>\"'")
    if is_junk(v):
        return "", "missing_website"

    v = re.sub(r"\s+", "", v)
    if v.lower().startswith("//"):
        v = "https:" + v
    if not re.match(r"^[a-zA-Z][a-zA-Z0-9+.-]*://", v):
        v = "https://" + v.lstrip("/")

    try:
        parts = urlsplit(v)
    except ValueError:
        return "", "invalid_website_url"

    if parts.scheme not in ("http", "https"):
        return "", "invalid_website_url"

    host = (parts.hostname or "").lower().strip(".")
    if not host or "." not in host or host in ("localhost",):
        return "", "invalid_website_url"
    if not re.match(r"^[a-z0-9.-]+$", host):
        try:
            host = host.encode("idna").decode("ascii")   # IDN -> punycode
        except (UnicodeError, ValueError):
            return "", "invalid_website_url"
    if not re.search(r"\.[a-z]{2,}$", host):
        return "", "invalid_website_url"

    port = f":{parts.port}" if parts.port and parts.port not in (80, 443) else ""
    path = re.sub(r"/+$", "", parts.path or "")

    query = ""
    if parts.query:
        keep = [kv for kv in parts.query.split("&")
                if kv and not kv.lower().startswith(("utm_", "fbclid", "gclid", "mc_"))]
        query = "&".join(keep)

    url = urlunsplit(("https", host + port, path, query, ""))
    return url, ""


def host_of(url: str) -> str:
    try:
        return (urlsplit(url).hostname or "").lower()
    except ValueError:
        return ""


def registrable_domain(url_or_host: str) -> str:
    """Best-effort eTLD+1 (handles the UK two-level suffixes we care about)."""
    host = url_or_host
    if "://" in url_or_host:
        host = host_of(url_or_host)
    host = (host or "").lower().strip(".")
    if host.startswith("www."):
        host = host[4:]
    labels = host.split(".")
    if len(labels) <= 2:
        return host
    if ".".join(labels[-2:]) in PUBLIC_SUFFIX_TWO_LEVEL:
        return ".".join(labels[-3:])
    return ".".join(labels[-2:])


def is_non_website_host(url: str) -> bool:
    return registrable_domain(url) in NON_WEBSITE_HOSTS


# --------------------------------------------------------------------------
# Misc
# --------------------------------------------------------------------------

SENIORITY_RANK = [
    (re.compile(r"\b(owner|founder|proprietor|ceo|managing director|md)\b", re.I), 100),
    (re.compile(r"\bdirector\b", re.I), 90),
    (re.compile(r"\b(registered manager|nominated individual)\b", re.I), 80),
    (re.compile(r"\b(operations manager|care manager|branch manager|general manager)\b", re.I), 70),
    (re.compile(r"\b(hr|human resources|people)\b", re.I), 60),
    (re.compile(r"\bmanager\b", re.I), 50),
]


def seniority_score(job_title: str) -> int:
    for pattern, score in SENIORITY_RANK:
        if pattern.search(job_title or ""):
            return score
    return 10


def log(msg: str) -> None:
    print(msg, file=sys.stderr, flush=True)
