"""STEP 2 — async website quality check.

For every clean lead, fetch the homepage once and score the site "bad" / "ok"
/ "good", recording the specific reasons behind the score.

Results stream to a JSONL cache as they complete, so an interrupted run
resumes instead of re-fetching. Delete the cache to force a fresh check.

Requires: pip install httpx
"""

from __future__ import annotations

import argparse
import asyncio
import csv
import datetime as dt
import json
import os
import re
import ssl
import sys
import time
from typing import Dict, List, Optional, Tuple
from urllib.parse import urlsplit, urlunsplit

try:
    import httpx
except ImportError:  # pragma: no cover
    raise SystemExit("check_sites.py needs httpx. Run:  pip install httpx")

from common import host_of, log, registrable_domain, write_csv

USER_AGENT = ("Mozilla/5.0 (compatible; LeadSiteAudit/1.0; "
              "website quality check; +mailto:abuse@localhost)")

MAX_HTML_BYTES = 1_500_000     # stop reading past this; homepages are far smaller
CURRENT_YEAR = dt.date.today().year

# --------------------------------------------------------------------------
# Fingerprints
# --------------------------------------------------------------------------
# quality: "cheap" and "dated" are penalised, "modern" earns a small bonus,
# "cms" is neutral on its own (WordPress is judged by theme and version).

BUILDER_SIGNATURES: List[Tuple[str, str, re.Pattern]] = [
    ("GoDaddy Website Builder", "cheap",
     re.compile(r"img\d*\.wsimg\.com|godaddysites\.com|starfieldtech|"
                r"websitebuilder\.godaddy", re.I)),
    ("Weebly", "cheap",
     re.compile(r"weebly\.com|editmysite\.com|weeblycloud|_W\.configDomain", re.I)),
    ("Jimdo", "cheap", re.compile(r"jimdo\.com|jimdofree\.com|jimstatic\.com", re.I)),
    ("IONOS/1&1 MyWebsite", "cheap",
     re.compile(r"mywebsite-editor\.com|1and1\.com/website-builder|ionos.*mywebsite", re.I)),
    ("Vistaprint/Yola/Moonfruit", "cheap",
     re.compile(r"vistaprintdigital|yola\.com|yolasite\.com|moonfruit\.com", re.I)),
    ("Google Sites / Business Site", "cheap",
     re.compile(r"sites\.google\.com|business\.site|gstatic\.com/sites", re.I)),
    ("Microsoft FrontPage", "dated", re.compile(r"frontpage|_vti_|mso-", re.I)),
    ("Adobe Dreamweaver / Muse", "dated",
     re.compile(r"content=\"Adobe (Dreamweaver|Muse)", re.I)),
    ("Wix (legacy)", "dated",
     re.compile(r"wixsite\.com|wix\.com/website|html5\.wixsite", re.I)),
    ("Wix (current)", "modern",
     re.compile(r"wix-thunderbolt|parastorage\.com|x-wix-|wixstatic\.com/.*thunderbolt|"
                r"server: *Pepyaka", re.I)),
    ("Squarespace", "modern", re.compile(r"squarespace\.com|static1\.squarespace", re.I)),
    ("Webflow", "modern", re.compile(r"webflow\.io|assets\.website-files\.com|"
                                     r"cdn\.prod\.website-files\.com", re.I)),
    ("Shopify", "modern", re.compile(r"cdn\.shopify\.com|myshopify\.com", re.I)),
    ("HubSpot CMS", "modern", re.compile(r"hs-sites\.com|hubspotusercontent", re.I)),
    ("Duda", "modern", re.compile(r"dudaone|d1\.awsstatic|multiscreensite\.com", re.I)),
    ("WordPress", "cms", re.compile(r"/wp-content/|/wp-includes/|wp-json", re.I)),
    ("Joomla", "cms", re.compile(r"/media/jui/|joomla", re.I)),
    ("Drupal", "cms", re.compile(r"/sites/default/files/|Drupal\.settings", re.I)),
]

# WordPress bundled themes. Anything up to twentynineteen is a strong signal of
# a site that was stood up once and never touched again.
WP_DEFAULT_THEMES = re.compile(
    r"/themes/(twenty(?:ten|eleven|twelve|thirteen|fourteen|fifteen|sixteen|"
    r"seventeen|nineteen|twenty|twentyone|twentytwo|twentythree|twentyfour|"
    r"twentyfive))/", re.I)
WP_STALE_THEMES = {
    "twentyten", "twentyeleven", "twentytwelve", "twentythirteen",
    "twentyfourteen", "twentyfifteen", "twentysixteen", "twentyseventeen",
    "twentynineteen",
}

PARKED_PATTERNS = re.compile(
    r"(this domain (name )?is for sale|buy this domain|domain for sale|"
    r"under construction|site (is )?coming soon|website coming soon|"
    r"account (has been )?suspended|default web ?page|apache2 (ubuntu|debian) default|"
    r"welcome to nginx|future home of|index of /|sedoparking|parkingcrew|"
    r"bodis\.com|this site is temporarily unavailable|"
    r"your new website is (almost )?ready|placeholder page)", re.I)

LEGACY_MARKUP = re.compile(
    r"<font\b|<center\b|<marquee\b|bgcolor=|<frameset\b|"
    r"text=\"#|link=\"#|topmargin=", re.I)

COPYRIGHT_RE = re.compile(
    r"(?:©|&copy;|&#169;|\(c\)|copyright)[^0-9]{0,30}"
    r"(?:(19|20)\d{2}\s*(?:[-–—]|to|&ndash;)\s*)?((?:19|20)\d{2})", re.I)

VIEWPORT_RE = re.compile(
    r"<meta[^>]+name=[\"']?viewport[\"']?[^>]*content=[\"']([^\"']*)", re.I)
GENERATOR_RE = re.compile(
    r"<meta[^>]+name=[\"']?generator[\"']?[^>]*content=[\"']([^\"']*)", re.I)
TITLE_RE = re.compile(r"<title[^>]*>(.*?)</title>", re.I | re.S)
STYLESHEET_RE = re.compile(r"<link[^>]+rel=[\"']?stylesheet", re.I)
SCRIPT_SRC_RE = re.compile(r"<script[^>]+src=", re.I)
IMG_RE = re.compile(r"<img\b", re.I)
HREF_RE = re.compile(r"href=[\"']([^\"'#]+)", re.I)
TAG_RE = re.compile(r"<[^>]+>")
WP_VERSION_RE = re.compile(r"WordPress\s+(\d+)\.(\d+)", re.I)


# --------------------------------------------------------------------------
# Fetching
# --------------------------------------------------------------------------

async def ssl_probe(host: str, timeout: float, port: int = 443) -> dict:
    """Verified TLS handshake, purely to read the certificate."""
    out: dict = {"ssl_valid": False, "ssl_error": "", "ssl_days_to_expiry": "",
                 "ssl_issuer": ""}
    if not host:
        out["ssl_error"] = "no_host"
        return out
    ctx = ssl.create_default_context()
    writer = None
    try:
        conn = asyncio.open_connection(host, port, ssl=ctx, server_hostname=host)
        reader, writer = await asyncio.wait_for(conn, timeout=timeout)
        cert = writer.get_extra_info("ssl_object").getpeercert() or {}
        out["ssl_valid"] = True
        not_after = cert.get("notAfter")
        if not_after:
            expiry = dt.datetime.strptime(not_after, "%b %d %H:%M:%S %Y %Z")
            out["ssl_days_to_expiry"] = (expiry - dt.datetime.utcnow()).days
        issuer = dict(x[0] for x in cert.get("issuer", []) if x)
        out["ssl_issuer"] = issuer.get("organizationName", "")
    except ssl.SSLCertVerificationError as exc:
        out["ssl_error"] = (getattr(exc, "verify_message", "") or str(exc))[:120]
    except asyncio.TimeoutError:
        out["ssl_error"] = "tls_timeout"
    except Exception as exc:
        out["ssl_error"] = f"{type(exc).__name__}"
    finally:
        if writer is not None:
            try:
                writer.close()
                await writer.wait_closed()
            except Exception:
                pass
    return out


async def fetch(client: "httpx.AsyncClient", url: str) -> dict:
    """GET a URL, capping how much body we read. Never raises."""
    started = time.perf_counter()
    try:
        async with client.stream("GET", url) as response:
            chunks: List[bytes] = []
            total = 0
            async for chunk in response.aiter_bytes():
                chunks.append(chunk)
                total += len(chunk)
                if total >= MAX_HTML_BYTES:
                    break
            body = b"".join(chunks)
            encoding = response.charset_encoding or "utf-8"
            try:
                html = body.decode(encoding, errors="replace")
            except LookupError:
                html = body.decode("utf-8", errors="replace")
            return {
                "ok": True,
                "status": response.status_code,
                "final_url": str(response.url),
                "html": html,
                "html_bytes": total,
                "elapsed": time.perf_counter() - started,
                "headers": {k.lower(): v for k, v in response.headers.items()},
                "error": "",
            }
    except Exception as exc:
        name = type(exc).__name__
        detail = str(exc)[:120]
        return {"ok": False, "status": 0, "final_url": url, "html": "",
                "html_bytes": 0, "elapsed": time.perf_counter() - started,
                "headers": {}, "error": f"{name}: {detail}" if detail else name}


def _is_tls_error(error: str) -> bool:
    return bool(re.search(r"SSL|CERTIFICATE|TLS", error, re.I))


async def fetch_with_fallbacks(secure: "httpx.AsyncClient",
                               insecure: "httpx.AsyncClient",
                               url: str) -> Tuple[dict, List[str]]:
    """Try https, then https-without-verification, then http, toggling www.

    Port, path and query are carried through every attempt — dropping them
    silently turns a working deep link into a false "dead site".
    """
    notes: List[str] = []
    parts = urlsplit(url)
    netloc = parts.netloc
    hostname = (parts.hostname or "").lower()
    alt_hostname = hostname[4:] if hostname.startswith("www.") else f"www.{hostname}"
    port_suffix = f":{parts.port}" if parts.port else ""
    alt_netloc = alt_hostname + port_suffix

    def build(scheme: str, where: str) -> str:
        return urlunsplit((scheme, where, parts.path or "", parts.query or "", ""))

    result = await fetch(secure, build("https", netloc))
    if result["ok"]:
        return result, notes

    if _is_tls_error(result["error"]):
        notes.append("tls_verification_failed")
        relaxed = await fetch(insecure, build("https", netloc))
        if relaxed["ok"]:
            return relaxed, notes

    plain = await fetch(secure, build("http", netloc))
    if plain["ok"]:
        notes.append("http_only")
        return plain, notes

    # DNS/connection failure is often just a missing (or extra) www.
    if hostname and re.search(
            r"ConnectError|NameResolution|getaddrinfo|ConnectTimeout",
            result["error"], re.I):
        for scheme in ("https", "http"):
            alt = await fetch(secure, build(scheme, alt_netloc))
            if alt["ok"]:
                notes.append(f"resolved_via_{alt_hostname}")
                if scheme == "http":
                    notes.append("http_only")
                return alt, notes

    return result, notes


# --------------------------------------------------------------------------
# Analysis
# --------------------------------------------------------------------------

def analyse_html(html: str, final_url: str, headers: Dict[str, str]) -> dict:
    """Pull the structural signals we score on out of the raw HTML."""
    haystack = html + " " + " ".join(f"{k}: {v}" for k, v in headers.items())
    text = TAG_RE.sub(" ", html)
    text = re.sub(r"\s+", " ", text).strip()

    viewport_match = VIEWPORT_RE.search(html)
    viewport = viewport_match.group(1).strip() if viewport_match else ""

    generator_match = GENERATOR_RE.search(html)
    generator = generator_match.group(1).strip() if generator_match else ""

    title_match = TITLE_RE.search(html)
    title = re.sub(r"\s+", " ", title_match.group(1)).strip()[:160] if title_match else ""

    builders: List[str] = []
    qualities: List[str] = []
    for label, quality, pattern in BUILDER_SIGNATURES:
        if pattern.search(haystack):
            builders.append(label)
            qualities.append(quality)
    # "Wix (current)" and "Wix (legacy)" can both match; current wins.
    if "Wix (current)" in builders and "Wix (legacy)" in builders:
        idx = builders.index("Wix (legacy)")
        builders.pop(idx)
        qualities.pop(idx)

    wp_theme = ""
    wp_theme_stale = False
    theme_match = WP_DEFAULT_THEMES.search(haystack)
    if theme_match:
        wp_theme = theme_match.group(1).lower()
        wp_theme_stale = wp_theme in WP_STALE_THEMES

    wp_version = ""
    version_match = WP_VERSION_RE.search(generator or haystack)
    if version_match:
        wp_version = f"{version_match.group(1)}.{version_match.group(2)}"

    years = [int(m.group(2)) for m in COPYRIGHT_RE.finditer(html)]
    copyright_year = max(years) if years else 0

    host = host_of(final_url)
    internal_paths = set()
    for href in HREF_RE.findall(html):
        href = href.strip()
        if href.startswith("/") and not href.startswith("//"):
            internal_paths.add(href.split("?")[0].rstrip("/") or "/")
        elif host and host in href:
            tail = href.split(host, 1)[1]
            internal_paths.add(tail.split("?")[0].rstrip("/") or "/")

    stylesheets = len(STYLESHEET_RE.findall(html))
    return {
        "title": title,
        "generator": generator,
        "has_viewport": bool(viewport),
        "viewport_responsive": "width=device-width" in viewport.lower(),
        "builders": builders,
        "builder_qualities": qualities,
        "wp_theme": wp_theme,
        "wp_theme_stale": wp_theme_stale,
        "wp_version": wp_version,
        "copyright_year": copyright_year,
        "stylesheets": stylesheets,
        "scripts": len(SCRIPT_SRC_RE.findall(html)),
        "images": len(IMG_RE.findall(html)),
        "internal_links": len(internal_paths),
        "text_length": len(text),
        "legacy_markup": bool(LEGACY_MARKUP.search(html)),
        "parked": bool(PARKED_PATTERNS.search(text[:4000])) or
                  bool(PARKED_PATTERNS.search(title)),
        "responsive_css": "@media" in html.lower(),
    }


def score_site(record: dict) -> Tuple[str, int, List[str], List[str]]:
    """Return (quality, score 0-100, reasons, hard_bad_triggers).

    Any hard trigger forces "bad" regardless of the numeric score. These are
    the faults a prospect can see for themselves in ten seconds, which is what
    makes the Website Offer an easy conversation to open.
    """
    reasons: List[str] = []
    triggers: List[str] = []
    score = 100

    # "responded" = we got an HTTP response at all; "reachable" additionally
    # requires a non-error status. Older cache rows only have "reachable".
    responded = record.get("responded")
    if responded is None:
        responded = record.get("reachable")
    if not responded:
        err = record.get("error", "") or "no response"
        if re.search(r"NameResolution|getaddrinfo", err, re.I):
            reasons.append("domain does not resolve (dead site)")
        elif re.search(r"Timeout", err, re.I):
            reasons.append("site timed out (no response)")
        else:
            reasons.append(f"site unreachable ({err.split(':')[0]})")
        return "bad", 0, reasons, ["unreachable"]

    status = record.get("status", 0)
    if status >= 500:
        reasons.append(f"server error on homepage (HTTP {status})")
        return "bad", 5, reasons, ["server_error"]
    if status >= 400:
        reasons.append(f"homepage returns HTTP {status}")
        return "bad", 10, reasons, ["http_error"]

    if record.get("parked"):
        reasons.append("parked / 'coming soon' / placeholder page, no real site")
        return "bad", 10, reasons, ["parked"]

    # --- SSL ---
    if record.get("http_only"):
        score -= 35
        triggers.append("no_https")
        reasons.append("no HTTPS at all — browsers flag it 'Not secure'")
    elif not record.get("ssl_valid"):
        score -= 35
        triggers.append("broken_ssl")
        err = record.get("ssl_error", "") or "invalid certificate"
        reasons.append(f"broken SSL certificate ({err[:60]})")
    else:
        days = record.get("ssl_days_to_expiry")
        if isinstance(days, int) and days < 21:
            score -= 5
            reasons.append(f"SSL certificate expires in {days} days")

    # --- Mobile ---
    if not record.get("has_viewport"):
        score -= 30
        triggers.append("not_mobile_responsive")
        reasons.append("no mobile viewport tag — not mobile responsive")
    elif not record.get("viewport_responsive"):
        score -= 15
        reasons.append("viewport tag present but not set to width=device-width")
    if not record.get("responsive_css") and not record.get("has_viewport"):
        score -= 5
        reasons.append("no responsive CSS media queries")

    # --- Builder / platform ---
    qualities = record.get("builder_qualities") or []
    builders = record.get("builders") or []
    if "cheap" in qualities:
        score -= 30
        triggers.append("low_end_builder")
        cheap = [b for b, q in zip(builders, qualities) if q == "cheap"]
        reasons.append(f"built on a low-end site builder ({', '.join(cheap)})")
    if "dated" in qualities:
        score -= 30
        triggers.append("legacy_platform")
        dated = [b for b, q in zip(builders, qualities) if q == "dated"]
        reasons.append(f"legacy platform / editor ({', '.join(dated)})")
    if "modern" in qualities:
        score += 8

    if record.get("wp_theme_stale"):
        score -= 20
        triggers.append("wp_default_theme")
        reasons.append(f"WordPress running an old default theme "
                       f"({record['wp_theme']}), never customised")
    if record.get("wp_version"):
        major = int(record["wp_version"].split(".")[0])
        if major < 6:
            score -= 10
            reasons.append(f"outdated WordPress {record['wp_version']} "
                           f"exposed in page source")

    # --- Plain / unstyled HTML ---
    if record.get("stylesheets", 0) == 0 and not builders:
        score -= 35
        triggers.append("plain_unstyled_html")
        reasons.append("plain unstyled HTML — no stylesheets at all")
    if record.get("legacy_markup"):
        score -= 15
        reasons.append("1990s-era markup (<font>/<center>/bgcolor/frames)")

    # --- Copyright freshness ---
    year = record.get("copyright_year", 0)
    if year:
        age = CURRENT_YEAR - year
        if age >= 6:
            score -= 25
            triggers.append("copyright_6y_stale")
            reasons.append(f"footer copyright still says {year} "
                           f"({age} years out of date)")
        elif age >= 3:
            score -= 15
            reasons.append(f"footer copyright still says {year} "
                           f"({age} years out of date)")
        elif age <= 1:
            score += 5
    else:
        score -= 3
        reasons.append("no copyright year found in the page")

    # --- Speed ---
    load = record.get("load_seconds", 0.0) or 0.0
    if load > 8:
        score -= 20
        reasons.append(f"very slow homepage ({load:.1f}s to load)")
    elif load > 5:
        score -= 12
        reasons.append(f"slow homepage ({load:.1f}s to load)")
    elif load > 3:
        score -= 5
        reasons.append(f"sluggish homepage ({load:.1f}s to load)")
    elif load < 1.5:
        score += 4

    # --- Depth / maintenance ---
    if record.get("internal_links", 0) < 4:
        score -= 12
        reasons.append("single-page site with almost no internal navigation")
    if record.get("html_bytes", 0) < 15000 and record.get("stylesheets", 0) < 2:
        score -= 10
        reasons.append("very small, thin homepage (looks like a template stub)")
    if record.get("text_length", 0) < 500:
        score -= 10
        reasons.append("almost no page content")
    if record.get("images", 0) == 0:
        score -= 5
        reasons.append("no images on the homepage")

    score = max(0, min(100, score))
    if triggers:
        quality = "bad"          # a visible, nameable fault outranks the score
    elif score >= 75:
        quality = "good"
    elif score >= 45:
        quality = "ok"
    else:
        quality = "bad"

    if not reasons:
        reasons.append("modern, secure, mobile-friendly and actively maintained")
    return quality, score, reasons, triggers


# --------------------------------------------------------------------------
# Orchestration
# --------------------------------------------------------------------------

async def check_one(lead: dict,
                    secure: "httpx.AsyncClient",
                    insecure: "httpx.AsyncClient",
                    sem: asyncio.Semaphore,
                    timeout: float) -> dict:
    url = lead["website"]
    parts = urlsplit(url)
    host = (parts.hostname or "").lower()
    tls_port = parts.port or 443
    async with sem:
        fetch_task = fetch_with_fallbacks(secure, insecure, url)
        tls_task = ssl_probe(host, timeout=min(timeout, 10.0), port=tls_port)
        (result, notes), tls = await asyncio.gather(fetch_task, tls_task)

    record: dict = {
        "lead_id": lead["lead_id"],
        "domain": lead["domain"],
        "website": url,
        "checked_at": dt.datetime.now(dt.timezone.utc).isoformat(timespec="seconds"),
        "responded": bool(result["ok"]),
        "reachable": bool(result["ok"]) and result["status"] < 400,
        "status": result["status"],
        "final_url": result["final_url"],
        "load_seconds": round(result["elapsed"], 2),
        "html_bytes": result["html_bytes"],
        "error": result["error"],
        "http_only": "http_only" in notes,
        "notes": "; ".join(notes),
        **tls,
    }
    if result["ok"]:
        record.update(analyse_html(result["html"], result["final_url"],
                                   result["headers"]))
        if record["http_only"]:
            record["ssl_valid"] = False
    else:
        record.setdefault("title", "")
        record["responded"] = False
        record["reachable"] = False

    quality, score, reasons, triggers = score_site(record)
    record["website_quality"] = quality
    record["website_score"] = score
    record["website_reasons"] = "; ".join(reasons)
    record["hard_bad_triggers"] = "; ".join(triggers)
    record["builders"] = "; ".join(record.get("builders") or [])
    record.pop("builder_qualities", None)
    return record


def load_cache(path: str) -> Dict[str, dict]:
    cache: Dict[str, dict] = {}
    if not os.path.exists(path):
        return cache
    with open(path, "r", encoding="utf-8") as fh:
        for line in fh:
            line = line.strip()
            if not line:
                continue
            try:
                record = json.loads(line)
            except json.JSONDecodeError:
                continue
            if record.get("lead_id"):
                cache[record["lead_id"]] = record
    return cache


async def run_checks(leads: List[dict],
                     outdir: str,
                     concurrency: int = 30,
                     timeout: float = 20.0,
                     refresh: bool = False) -> List[dict]:
    os.makedirs(outdir, exist_ok=True)
    cache_path = os.path.join(outdir, "site_checks_cache.jsonl")
    if refresh and os.path.exists(cache_path):
        os.remove(cache_path)

    cache = load_cache(cache_path)
    todo = [l for l in leads if l["lead_id"] not in cache]
    log(f"[sites] {len(leads)} leads | {len(cache)} cached | {len(todo)} to check "
        f"| concurrency={concurrency}")

    results: List[dict] = [cache[l["lead_id"]] for l in leads if l["lead_id"] in cache]

    if todo:
        limits = httpx.Limits(max_connections=concurrency,
                              max_keepalive_connections=concurrency)
        common_kwargs = dict(
            follow_redirects=True,
            timeout=httpx.Timeout(timeout, connect=min(timeout, 10.0)),
            limits=limits,
            headers={"User-Agent": USER_AGENT,
                     "Accept": "text/html,application/xhtml+xml",
                     "Accept-Language": "en-GB,en;q=0.9"},
        )
        sem = asyncio.Semaphore(concurrency)
        started = time.perf_counter()
        done = 0

        cache_fh = open(cache_path, "a", encoding="utf-8")
        try:
            async with httpx.AsyncClient(verify=True, **common_kwargs) as secure, \
                       httpx.AsyncClient(verify=False, **common_kwargs) as insecure:
                tasks = [asyncio.create_task(
                            check_one(l, secure, insecure, sem, timeout))
                         for l in todo]
                for coro in asyncio.as_completed(tasks):
                    record = await coro
                    results.append(record)
                    cache_fh.write(json.dumps(record, ensure_ascii=False) + "\n")
                    cache_fh.flush()
                    done += 1
                    if done % 25 == 0 or done == len(todo):
                        rate = done / max(time.perf_counter() - started, 0.01)
                        remaining = (len(todo) - done) / max(rate, 0.01)
                        log(f"[sites] {done}/{len(todo)} checked "
                            f"({rate:.1f}/s, ~{remaining:.0f}s left)")
        finally:
            cache_fh.close()

    order = {l["lead_id"]: i for i, l in enumerate(leads)}
    results.sort(key=lambda r: order.get(r["lead_id"], 10**9))
    return results


SITE_COLUMNS = [
    "lead_id", "domain", "website", "final_url", "website_quality",
    "website_score", "website_reasons", "responded", "reachable", "status",
    "load_seconds",
    "html_bytes", "ssl_valid", "ssl_days_to_expiry", "ssl_issuer", "ssl_error",
    "http_only", "has_viewport", "viewport_responsive", "responsive_css",
    "builders", "generator", "wp_theme", "wp_version", "copyright_year",
    "stylesheets", "scripts", "images", "internal_links", "text_length",
    "legacy_markup", "parked", "hard_bad_triggers", "title", "error",
    "notes", "checked_at",
]


def main() -> None:
    ap = argparse.ArgumentParser(description="STEP 2 — check website quality")
    ap.add_argument("--leads", default="output/clean_leads.csv")
    ap.add_argument("--outdir", default="output")
    ap.add_argument("--concurrency", type=int, default=30)
    ap.add_argument("--timeout", type=float, default=20.0)
    ap.add_argument("--limit", type=int, default=0, help="check only the first N")
    ap.add_argument("--refresh", action="store_true", help="ignore the cache")
    args = ap.parse_args()

    with open(args.leads, "r", encoding="utf-8-sig", newline="") as fh:
        leads = list(csv.DictReader(fh))
    if args.limit:
        leads = leads[: args.limit]

    results = asyncio.run(run_checks(leads, args.outdir, args.concurrency,
                                     args.timeout, args.refresh))
    out_path = os.path.join(args.outdir, "site_checks.csv")
    write_csv(out_path, results, SITE_COLUMNS)
    log(f"[sites] wrote {out_path}")


if __name__ == "__main__":
    main()
