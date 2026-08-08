"""Tests for STEP 1 normalization and STEP 3 segmentation.

Run:  python3 tests/test_segmentation.py
"""

from __future__ import annotations

import csv
import os
import sys
import tempfile

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from clean_leads import CLEAN_COLUMNS, normalize_row  # noqa: E402
from common import (normalize_email, normalize_phone_uk, normalize_url,  # noqa: E402
                    registrable_domain, write_csv)
from segment_leads import (INSTANTLY_COLUMNS, notes_for_hr_offer,  # noqa: E402
                           notes_for_website_offer, rank_issues, segment)

FAILURES: list = []


def check(name: str, condition: bool, detail: str = "") -> None:
    if condition:
        print(f"  PASS  {name}")
    else:
        print(f"  FAIL  {name}  {detail}")
        FAILURES.append(name)


def test_normalizers() -> None:
    print("\nfield normalizers")

    for raw, expected in [
        ("http://Example.co.uk/", "https://example.co.uk"),
        ("example.co.uk", "https://example.co.uk"),
        ("HTTPS://WWW.Example.CO.UK///", "https://www.example.co.uk"),
        ("//example.co.uk/about/", "https://example.co.uk/about"),
        ("https://example.co.uk/?utm_source=x&id=7", "https://example.co.uk?id=7"),
    ]:
        got, problem = normalize_url(raw)
        check(f"url {raw!r} -> {expected!r}", got == expected and not problem,
              f"got {got!r} {problem}")

    for raw in ("n/a", "", "-", "not available"):
        got, problem = normalize_url(raw)
        check(f"url junk {raw!r} rejected", problem == "missing_website", problem)

    check("url with no dot rejected", normalize_url("localhost")[1] != "")

    for raw, expected in [
        ("020 7946 0123", "+442079460123"),
        ("+44 20 7946 0123", "+442079460123"),
        ("00442079460123", "+442079460123"),
        ("07700 900123", "+447700900123"),
        ("(0113) 245 4332", "+441132454332"),
        ("0113 245 4332 ext 22", "+441132454332"),
    ]:
        got, _ = normalize_phone_uk(raw)
        check(f"phone {raw!r} -> {expected}", got == expected, f"got {got}")

    check("blank phone stays blank", normalize_phone_uk("")[0] == "")

    for raw, expected in [
        ("  John.Smith@Example.CO.UK ", "john.smith@example.co.uk"),
        ("Jane Doe <jane@care.co.uk>", "jane@care.co.uk"),
        ("mailto:bob@care.co.uk", "bob@care.co.uk"),
        ("a@care.co.uk, b@care.co.uk", "a@care.co.uk"),
    ]:
        check(f"email {raw!r}", normalize_email(raw) == expected,
              normalize_email(raw))

    # eTLD+1: subdomains collapse so two contacts on different subdomains of
    # one company are treated as the same domain by the dedupe step.
    for raw, expected in [
        ("https://www.sub.example.co.uk/x", "example.co.uk"),
        ("https://www.example.co.uk", "example.co.uk"),
        ("https://example.com", "example.com"),
        ("https://care.org.uk", "care.org.uk"),
        ("https://branch.care.org.uk", "care.org.uk"),
    ]:
        check(f"domain {raw} -> {expected}",
              registrable_domain(raw) == expected, registrable_domain(raw))


def test_row_validation() -> None:
    print("\nrow validation")

    good = normalize_row({
        "_row_number": 2, "_raw": {},
        "first_name": "sarah", "last_name": "o'brien",
        "job_title": "REGISTERED MANAGER", "email": "Sarah@Care.co.uk",
        "phone": "020 7946 0123", "company": "sunrise care ltd",
        "website": "http://care.co.uk/", "email_status": "good",
        "keywords": "dementia care, live-in care, domiciliary care",
    })
    check("valid row has no reasons", good["_reasons"] == [], str(good["_reasons"]))
    check("name title-cased", good["first_name"] == "Sarah")
    check("apostrophe name cased", good["last_name"] == "O'Brien", good["last_name"])
    check("job title cased", good["job_title"] == "Registered Manager",
          good["job_title"])
    check("seniority scored", good["seniority"] == 80, str(good["seniority"]))
    check("service focus extracted",
          "live-in care" in good["service_focus"] and
          "dementia care" in good["service_focus"], good["service_focus"])

    missing = normalize_row({"_row_number": 3, "_raw": {}, "first_name": "Bob",
                             "email": "", "company": "X", "website": "x.co.uk"})
    check("missing email flagged", "missing_email" in missing["_reasons"])

    bad_email = normalize_row({"_row_number": 4, "_raw": {}, "first_name": "Bob",
                               "email": "bob@@care", "company": "X",
                               "website": "x.co.uk"})
    check("malformed email flagged",
          "invalid_email_format" in bad_email["_reasons"])

    placeholder = normalize_row({"_row_number": 5, "_raw": {}, "first_name": "Bob",
                                 "email": "bob@example.com", "company": "X",
                                 "website": "x.co.uk"})
    check("placeholder domain flagged",
          "placeholder_email_domain" in placeholder["_reasons"])

    noreply = normalize_row({"_row_number": 6, "_raw": {}, "first_name": "Bob",
                             "email": "noreply@care.co.uk", "company": "X",
                             "website": "x.co.uk"})
    check("noreply flagged", "unmonitored_mailbox" in noreply["_reasons"])

    role = normalize_row({"_row_number": 7, "_raw": {}, "first_name": "Bob",
                          "email": "info@care.co.uk", "company": "X",
                          "website": "x.co.uk"})
    check("role email kept but typed", role["_reasons"] == [] and
          role["email_type"] == "role", role["email_type"])

    social = normalize_row({"_row_number": 8, "_raw": {}, "first_name": "Bob",
                            "email": "bob@care.co.uk", "company": "X",
                            "website": "https://facebook.com/carepage"})
    check("social-only site kept but flagged",
          social["_reasons"] == [] and
          "no_own_website" in social["flags"], social["flags"])

    risky = normalize_row({"_row_number": 9, "_raw": {}, "first_name": "Bob",
                           "email": "bob@care.co.uk", "company": "X",
                           "website": "care.co.uk", "email_status": "risky"})
    check("risky verifier status flagged not dropped",
          risky["_reasons"] == [] and "verifier_risky" in risky["flags"])

    invalid = normalize_row({"_row_number": 10, "_raw": {}, "first_name": "Bob",
                             "email": "bob@care.co.uk", "company": "X",
                             "website": "care.co.uk", "email_status": "invalid"})
    check("verifier 'invalid' sends row to review",
          any("verifier_says" in r for r in invalid["_reasons"]),
          str(invalid["_reasons"]))


def test_issue_ranking() -> None:
    print("\nissue ranking and notes")

    reasons = ("footer copyright still says 2018 (8 years out of date); "
               "no mobile viewport tag — not mobile responsive; "
               "slow homepage (6.2s to load)")
    ranked = rank_issues(reasons)
    check("mobile issue ranked first",
          "not mobile responsive" in ranked[0], ranked[0])
    check("all issues retained", len(ranked) == 3, str(len(ranked)))

    positive = rank_issues("modern, secure, mobile-friendly and actively maintained")
    check("positive reason produces no issues", positive == [], str(positive))

    lead = {"domain": "sunrisecare.co.uk", "company": "Sunrise Care",
            "service_focus": "dementia care; live-in care",
            "website_score": "18", "website_quality": "bad"}
    note = notes_for_website_offer(lead, ranked)
    check("A note names the domain", "sunrisecare.co.uk" in note, note)
    check("A note leads with the mobile issue",
          "not mobile responsive" in note, note)
    check("A note mentions the specialism", "dementia care" in note, note)

    hr = notes_for_hr_offer({"company": "Sunrise Care", "website_quality": "good",
                             "website_score": "88",
                             "service_focus": "live-in care"})
    check("B note warns off web design", "do NOT pitch web design" in hr, hr)
    check("B note carries the HR angle", "DBS" in hr, hr)


def test_segment_split() -> None:
    print("\nsegment split")

    leads = [
        {"lead_id": "ld_1", "first_name": "A", "last_name": "One",
         "email": "a@one.co.uk", "company": "One Care", "website": "https://one.co.uk",
         "domain": "one.co.uk", "phone": "+441111111111", "job_title": "Director",
         "service_focus": "dementia care", "email_status": "good",
         "email_type": "personal", "linkedin": ""},
        {"lead_id": "ld_2", "first_name": "B", "last_name": "Two",
         "email": "b@two.co.uk", "company": "Two Care", "website": "https://two.co.uk",
         "domain": "two.co.uk", "phone": "", "job_title": "Registered Manager",
         "service_focus": "live-in care", "email_status": "good",
         "email_type": "personal", "linkedin": ""},
        {"lead_id": "ld_3", "first_name": "C", "last_name": "Three",
         "email": "c@three.co.uk", "company": "Three Care",
         "website": "https://three.co.uk", "domain": "three.co.uk", "phone": "",
         "job_title": "HR Manager", "service_focus": "", "email_status": "risky",
         "email_type": "role", "linkedin": ""},
        {"lead_id": "ld_4", "first_name": "D", "last_name": "Four",
         "email": "d@four.co.uk", "company": "Four Care",
         "website": "https://four.co.uk", "domain": "four.co.uk", "phone": "",
         "job_title": "Director", "service_focus": "", "email_status": "good",
         "email_type": "personal", "linkedin": ""},
    ]
    checks = [
        {"lead_id": "ld_1", "website_quality": "bad", "website_score": "15",
         "final_url": "https://one.co.uk",
         "website_reasons": "no mobile viewport tag — not mobile responsive"},
        {"lead_id": "ld_2", "website_quality": "good", "website_score": "92",
         "final_url": "https://two.co.uk",
         "website_reasons": "modern, secure, mobile-friendly and actively maintained"},
        {"lead_id": "ld_3", "website_quality": "ok", "website_score": "60",
         "final_url": "https://three.co.uk",
         "website_reasons": "slow homepage (5.4s to load)"},
        # ld_4 deliberately has no check row
    ]

    with tempfile.TemporaryDirectory() as tmp:
        leads_path = os.path.join(tmp, "clean_leads.csv")
        checks_path = os.path.join(tmp, "site_checks.csv")
        write_csv(leads_path, leads, CLEAN_COLUMNS)
        write_csv(checks_path, checks,
                  ["lead_id", "website_quality", "website_score",
                   "website_reasons", "final_url"])

        stats = segment(leads_path, checks_path, tmp)
        check("A gets only the bad site", stats["segment_a"] == 1,
              str(stats["segment_a"]))
        check("B gets ok + good", stats["segment_b"] == 2, str(stats["segment_b"]))
        check("unchecked lead is not silently dropped",
              stats["unchecked"] == 1, str(stats["unchecked"]))
        check("unchecked file written", "unchecked" in stats["paths"])

        with open(stats["paths"]["segment_a"], newline="", encoding="utf-8") as fh:
            rows_a = list(csv.DictReader(fh))
        with open(stats["paths"]["segment_b"], newline="", encoding="utf-8") as fh:
            rows_b = list(csv.DictReader(fh))

        check("A columns match the Instantly spec",
              list(rows_a[0].keys()) == INSTANTLY_COLUMNS)
        for required in ("first_name", "last_name", "email", "company",
                         "website", "personalization_notes"):
            check(f"A has required column {required}", required in rows_a[0])
            check(f"A column {required} is populated", bool(rows_a[0][required]),
                  repr(rows_a[0][required]))
        check("A row is the bad-site lead", rows_a[0]["email"] == "a@one.co.uk")
        check("A website_issue populated", bool(rows_a[0]["website_issue"]),
              rows_a[0]["website_issue"])
        check("B notes never pitch web design",
              all("do NOT pitch web design" in r["personalization_notes"]
                  for r in rows_b))
        check("every B row has notes",
              all(r["personalization_notes"] for r in rows_b))


if __name__ == "__main__":
    test_normalizers()
    test_row_validation()
    test_issue_ranking()
    test_segment_split()
    print()
    if FAILURES:
        print(f"{len(FAILURES)} FAILED: {FAILURES}")
        sys.exit(1)
    print("all tests passed")
