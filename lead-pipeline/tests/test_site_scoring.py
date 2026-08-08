"""Fixture tests for the STEP 2 analyser, scorer and async fetch plumbing.

Run:  python3 tests/test_site_scoring.py

The scoring tests are pure functions over fixture HTML, so they assert exact
behaviour without touching the network. The last test starts a throwaway HTTP
server on localhost to exercise the real async fetch/cache path.
"""

from __future__ import annotations

import asyncio
import datetime as dt
import functools
import http.server
import os
import socket
import sys
import tempfile
import threading

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from check_sites import analyse_html, run_checks, score_site  # noqa: E402

YEAR = dt.date.today().year
FAILURES: list = []


def check(name: str, condition: bool, detail: str = "") -> None:
    if condition:
        print(f"  PASS  {name}")
    else:
        print(f"  FAIL  {name}  {detail}")
        FAILURES.append(name)


def base_record(**overrides) -> dict:
    """A healthy site; individual tests degrade one thing at a time."""
    record = {
        "reachable": True, "status": 200, "ssl_valid": True,
        "ssl_days_to_expiry": 200, "http_only": False, "parked": False,
        "has_viewport": True, "viewport_responsive": True, "responsive_css": True,
        "builders": [], "builder_qualities": [], "wp_theme": "",
        "wp_theme_stale": False, "wp_version": "", "copyright_year": YEAR,
        "stylesheets": 5, "scripts": 8, "images": 14, "internal_links": 18,
        "text_length": 4200, "legacy_markup": False, "html_bytes": 90_000,
        "load_seconds": 1.1, "error": "",
    }
    record.update(overrides)
    return record


# --------------------------------------------------------------------------
# Fixture pages
# --------------------------------------------------------------------------

MODERN = f"""<!doctype html><html><head><title>Bright Home Care</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/css/main.css"><link rel="stylesheet" href="/css/b.css">
<script src="https://cdn.prod.website-files.com/app.js"></script>
<style>@media (max-width: 700px) {{ .nav {{ display:none }} }}</style></head>
<body><nav><a href="/about">About</a><a href="/services">Services</a>
<a href="/careers">Careers</a><a href="/contact">Contact</a>
<a href="/live-in-care">Live-in care</a><a href="/dementia">Dementia</a></nav>
<img src="/a.jpg"><img src="/b.jpg"><img src="/c.jpg">
<p>{'We provide compassionate home care across the county. ' * 40}</p>
<footer>&copy; {YEAR} Bright Home Care Ltd</footer></body></html>"""

WP_STALE = f"""<!doctype html><html><head><title>Oakfield Care</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="generator" content="WordPress 5.4.2">
<link rel="stylesheet" href="/wp-content/themes/twentyfifteen/style.css">
</head><body><a href="/about">About</a><a href="/contact">Contact</a>
<img src="/wp-content/uploads/logo.png">
<p>{'Care in your own home. ' * 30}</p>
<footer>Copyright &copy; 2017 Oakfield Care</footer></body></html>"""

GODADDY = """<!doctype html><html><head><title>Sunrise Carers</title>
<link rel="stylesheet" href="https://img1.wsimg.com/theme/style.css">
<script src="https://img1.wsimg.com/nextgen/site.js"></script></head>
<body><a href="/home">Home</a><p>Welcome to Sunrise Carers.</p>
<footer>&copy; 2019</footer></body></html>"""

PLAIN_1990S = """<html><head><title>Homely Care Services</title></head>
<body bgcolor="#FFFFFF" text="#000000">
<center><font size="5" face="Arial">Homely Care Services</font></center>
<table width="600"><tr><td>Call us on 01234 567890</td></tr></table>
<p>Copyright 2011 Homely Care</p></body></html>"""

PARKED = """<html><head><title>Domain for sale</title></head><body>
<h1>This domain is for sale</h1><p>Buy this domain today.</p></body></html>"""

WIX_LEGACY = """<html><head><title>Care Co</title>
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" href="/s.css">
<script src="https://static.wixstatic.com/legacy.js"></script></head>
<body><a href="https://carecoxyz.wixsite.com/home">Home</a>
<p>Care services.</p><footer>&copy; 2020</footer></body></html>"""

MEDIOCRE = f"""<!doctype html><html><head><title>Willow Care</title>
<meta name="viewport" content="width=1024">
<link rel="stylesheet" href="/style.css"><link rel="stylesheet" href="/x.css">
</head><body><a href="/about">About</a><a href="/services">Services</a>
<a href="/contact">Contact</a><a href="/jobs">Jobs</a><a href="/news">News</a>
<p>{'Domiciliary care provider. ' * 40}</p>
<footer>&copy; {YEAR - 4} Willow Care Ltd</footer></body></html>"""


def test_analyse() -> None:
    print("\nanalyse_html()")
    modern = analyse_html(MODERN, "https://brighthomecare.co.uk", {})
    check("modern: viewport detected",
          modern["has_viewport"] and modern["viewport_responsive"])
    check("modern: Webflow fingerprinted", "Webflow" in modern["builders"],
          str(modern["builders"]))
    check("modern: current copyright year",
          modern["copyright_year"] == YEAR, str(modern["copyright_year"]))
    check("modern: internal links counted",
          modern["internal_links"] >= 6, str(modern["internal_links"]))
    check("modern: responsive CSS seen", modern["responsive_css"])

    wp = analyse_html(WP_STALE, "https://oakfieldcare.co.uk", {})
    check("wordpress: detected", "WordPress" in wp["builders"], str(wp["builders"]))
    check("wordpress: stale default theme flagged",
          wp["wp_theme"] == "twentyfifteen" and wp["wp_theme_stale"])
    check("wordpress: version parsed", wp["wp_version"] == "5.4", wp["wp_version"])
    check("wordpress: 2017 copyright", wp["copyright_year"] == 2017,
          str(wp["copyright_year"]))

    gd = analyse_html(GODADDY, "https://sunrisecarers.co.uk", {})
    check("godaddy: builder fingerprinted",
          "GoDaddy Website Builder" in gd["builders"], str(gd["builders"]))
    check("godaddy: no viewport", not gd["has_viewport"])

    plain = analyse_html(PLAIN_1990S, "https://homelycare.co.uk", {})
    check("plain html: no stylesheets", plain["stylesheets"] == 0)
    check("plain html: legacy markup detected", plain["legacy_markup"])
    check("plain html: no builder matched", plain["builders"] == [],
          str(plain["builders"]))

    parked = analyse_html(PARKED, "https://gone.co.uk", {})
    check("parked: detected", parked["parked"])

    wix = analyse_html(WIX_LEGACY, "https://careco.co.uk", {})
    check("wix legacy: detected", "Wix (legacy)" in wix["builders"],
          str(wix["builders"]))

    headers_only = analyse_html("<html><body>hi</body></html>", "https://x.co.uk",
                                {"x-wix-request-id": "abc",
                                 "server": "Pepyaka"})
    check("header fingerprints are read",
          any("Wix" in b for b in headers_only["builders"]),
          str(headers_only["builders"]))


def test_scoring() -> None:
    print("\nscore_site()")

    quality, score, reasons, triggers = score_site(base_record())
    check("healthy site -> good", quality == "good" and score >= 75,
          f"{quality} {score}")
    check("healthy site has no triggers", triggers == [], str(triggers))

    quality, score, reasons, triggers = score_site(
        base_record(reachable=False, error="ConnectTimeout: timed out"))
    check("timeout -> bad/0", quality == "bad" and score == 0)
    check("timeout reason is readable",
          any("timed out" in r for r in reasons), str(reasons))

    quality, _, reasons, _ = score_site(
        base_record(reachable=False, error="ConnectError: [Errno -2] Name or "
                                           "service not known getaddrinfo"))
    check("dns failure -> bad", quality == "bad")
    check("dns reason mentions dead site",
          any("does not resolve" in r for r in reasons), str(reasons))

    quality, _, _, triggers = score_site(base_record(status=404))
    check("404 -> bad", quality == "bad" and triggers == ["http_error"])

    quality, _, _, triggers = score_site(base_record(parked=True))
    check("parked -> bad", quality == "bad" and triggers == ["parked"])

    # The case that motivated hard triggers: everything else is fine.
    quality, score, reasons, triggers = score_site(
        base_record(has_viewport=False, viewport_responsive=False))
    check("no viewport -> bad despite decent score",
          quality == "bad" and "not_mobile_responsive" in triggers,
          f"{quality} {score} {triggers}")

    quality, _, reasons, triggers = score_site(base_record(http_only=True,
                                                           ssl_valid=False))
    check("http-only -> bad", quality == "bad" and "no_https" in triggers)
    check("http-only reason mentions Not secure",
          any("Not secure" in r for r in reasons), str(reasons))

    quality, _, _, triggers = score_site(
        base_record(ssl_valid=False, ssl_error="certificate has expired"))
    check("expired cert -> bad", quality == "bad" and "broken_ssl" in triggers)

    quality, _, _, triggers = score_site(
        base_record(builders=["GoDaddy Website Builder"],
                    builder_qualities=["cheap"]))
    check("cheap builder -> bad", quality == "bad" and "low_end_builder" in triggers)

    quality, _, _, triggers = score_site(
        base_record(builders=["WordPress"], builder_qualities=["cms"],
                    wp_theme="twentyfifteen", wp_theme_stale=True))
    check("wp default theme -> bad",
          quality == "bad" and "wp_default_theme" in triggers)

    quality, _, reasons, triggers = score_site(base_record(copyright_year=YEAR - 7))
    check("7-year-old copyright -> bad",
          quality == "bad" and "copyright_6y_stale" in triggers)
    check("copyright reason names the year",
          any(str(YEAR - 7) in r for r in reasons), str(reasons))

    quality, score, reasons, triggers = score_site(base_record(copyright_year=YEAR - 4))
    check("4-year-old copyright penalised but not a hard trigger",
          triggers == [] and score < 90, f"{score} {triggers}")

    quality, _, _, triggers = score_site(
        base_record(stylesheets=0, builders=[], legacy_markup=True))
    check("plain unstyled html -> bad",
          quality == "bad" and "plain_unstyled_html" in triggers)

    # A genuinely middling site: no hard fault, several soft penalties.
    quality, score, reasons, triggers = score_site(
        base_record(viewport_responsive=False, copyright_year=YEAR - 4,
                    load_seconds=5.5, images=0, internal_links=5,
                    responsive_css=False))
    check("middling site -> ok", quality == "ok", f"{quality} {score} {triggers}")
    check("middling site keeps its reasons", len(reasons) >= 3, str(reasons))

    quality, score, _, _ = score_site(base_record(load_seconds=9.0))
    check("very slow site penalised", score <= 85, str(score))

    # End-to-end over a fixture page, as the real pipeline would see it.
    record = base_record(**analyse_html(MEDIOCRE, "https://willowcare.co.uk", {}))
    record["load_seconds"] = 4.0
    quality, score, reasons, triggers = score_site(record)
    check("MEDIOCRE fixture -> ok, no hard trigger",
          quality == "ok" and not triggers, f"{quality} {score} {triggers}")

    record = base_record(**analyse_html(GODADDY, "https://sunrisecarers.co.uk", {}))
    quality, score, reasons, triggers = score_site(record)
    check("GODADDY fixture -> bad", quality == "bad", f"{quality} {score}")
    check("GODADDY reasons name the builder",
          any("GoDaddy" in r for r in reasons), str(reasons))


# --------------------------------------------------------------------------
# Async plumbing, against a real (local) HTTP server
# --------------------------------------------------------------------------

ROUTES = {
    "/good": (200, MODERN),
    "/godaddy": (200, GODADDY),
    "/plain": (200, PLAIN_1990S),
    "/parked": (200, PARKED),
    "/missing": (404, "<html><body>Not found</body></html>"),
}


class Handler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):  # noqa: N802
        status, body = ROUTES.get(self.path, (404, "<html>nope</html>"))
        payload = body.encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Content-Length", str(len(payload)))
        self.end_headers()
        self.wfile.write(payload)

    def log_message(self, *args):
        pass


def test_async_fetch() -> None:
    print("\nrun_checks() against a local server")
    sock = socket.socket()
    sock.bind(("127.0.0.1", 0))
    port = sock.getsockname()[1]
    sock.close()

    server = http.server.ThreadingHTTPServer(("127.0.0.1", port), Handler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    try:
        leads = [{"lead_id": f"ld_{name.strip('/')}",
                  "domain": f"127.0.0.1:{port}",
                  "website": f"http://127.0.0.1:{port}{name}"}
                 for name in ROUTES]
        with tempfile.TemporaryDirectory() as tmp:
            results = asyncio.run(run_checks(leads, tmp, concurrency=5, timeout=10))
            by_id = {r["lead_id"]: r for r in results}

            check("all leads returned", len(results) == len(leads),
                  f"{len(results)}/{len(leads)}")
            check("/good reachable", by_id["ld_good"]["reachable"],
                  by_id["ld_good"].get("error", ""))
            check("/good parsed: viewport seen", by_id["ld_good"]["has_viewport"])
            check("/good parsed: Webflow fingerprinted",
                  "Webflow" in by_id["ld_good"]["builders"],
                  by_id["ld_good"]["builders"])
            # Served over plain http, so no_https is the correct verdict here.
            check("/good is bad only because of no_https",
                  by_id["ld_good"]["hard_bad_triggers"] == "no_https",
                  by_id["ld_good"]["hard_bad_triggers"])
            check("/godaddy flagged as bad",
                  by_id["ld_godaddy"]["website_quality"] == "bad")
            check("/godaddy names the builder",
                  "GoDaddy" in by_id["ld_godaddy"]["builders"],
                  by_id["ld_godaddy"]["builders"])
            check("/plain detected as unstyled",
                  "plain_unstyled_html" in by_id["ld_plain"]["hard_bad_triggers"],
                  by_id["ld_plain"]["hard_bad_triggers"])
            check("/parked detected",
                  "parked" in by_id["ld_parked"]["hard_bad_triggers"],
                  by_id["ld_parked"]["hard_bad_triggers"])
            check("/missing is bad via http_error",
                  by_id["ld_missing"]["hard_bad_triggers"] == "http_error",
                  by_id["ld_missing"]["hard_bad_triggers"])
            check("load time recorded",
                  isinstance(by_id["ld_good"]["load_seconds"], float))

            # Second run must come entirely from the JSONL cache.
            cached = asyncio.run(run_checks(leads, tmp, concurrency=5, timeout=10))
            check("cache replays every lead", len(cached) == len(leads))
            check("cache preserves scores",
                  {r["lead_id"]: r["website_score"] for r in cached} ==
                  {r["lead_id"]: r["website_score"] for r in results})
    finally:
        server.shutdown()
        server.server_close()


if __name__ == "__main__":
    test_analyse()
    test_scoring()
    test_async_fetch()
    print()
    if FAILURES:
        print(f"{len(FAILURES)} FAILED: {FAILURES}")
        sys.exit(1)
    print("all tests passed")
