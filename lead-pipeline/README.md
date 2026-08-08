# Lead pipeline — clean, audit, segment

Takes a raw UK home care lead list (`.xlsx` or `.csv`), cleans and dedupes it,
checks every lead's website, and splits the result into two Instantly-ready
campaigns:

| Segment | Who's in it | The pitch |
|---|---|---|
| **A — Website Offer** | website scored `bad` | their site is old / broken / template — clear improvement opportunity |
| **B — HR Automation Offer** | website scored `ok` or `good` | site is fine, so lead with onboarding / compliance automation |

Franchise branch owners get their own copy of each file (`..._FRANCHISE.csv`) —
same offer, but they share a mail domain with head office, so send them slowly.

## Install

```bash
pip install -r requirements.txt
```

New to the command line? **[SETUP.md](SETUP.md)** walks through the whole thing
step by step for Mac and Windows.

Needs Python 3.9+. `openpyxl` is only required for `.xlsx` input.

**Network:** STEP 2 makes one outbound HTTPS request per lead. Run it somewhere
with unrestricted outbound access — behind a filtering proxy every site comes
back "unreachable" and every lead lands in Segment A.

## Usage

```bash
# Test run first — audits 50 randomly sampled leads
python3 run_pipeline.py --input leads.xlsx --sample 50

# Full run
python3 run_pipeline.py --input leads.xlsx
```

Results are cached per lead in `output/site_checks_cache.jsonl`, so an
interrupted run picks up where it left off and re-runs cost nothing. Pass
`--refresh` to force a fresh check.

At ~30 concurrent requests, 800 sites takes roughly 5–10 minutes.

### Useful flags

| Flag | Default | What it does |
|---|---|---|
| `--sample N` | off | audit only N randomly sampled leads |
| `--concurrency N` | 30 | parallel site checks; 50–75 is fine on a good connection |
| `--timeout S` | 20 | per-request timeout in seconds |
| `--max-per-domain N` | 1 | contacts to keep per company domain |
| `--no-franchise-branches` | off | collapse franchise networks to one contact too |
| `--franchise-in-main` | off | put franchise branches in the main files |
| `--drop-risky` | off | send catch-all / "risky" verified emails to needs_review |
| `--refresh` | off | ignore the cache and re-check every site |
| `--sheet NAME` | first | which worksheet to read from an `.xlsx` |
| `--map JSON` | — | override column detection, e.g. `'{"email":"Work Email"}'` |
| `--preview N` | 10 | rows to print from each output file |

You can also run the steps individually: `clean_leads.py`, `check_sites.py`,
`segment_leads.py` each have the same CLI style.

## Output files

| File | Contents |
|---|---|
| `segment_a_website_offer.csv` | **Instantly import** — leads with bad websites |
| `segment_b_hr_automation_offer.csv` | **Instantly import** — leads with ok/good websites |
| `segment_a_website_offer_FRANCHISE.csv` | franchise branch owners, bad sites — send slowly |
| `segment_b_hr_automation_offer_FRANCHISE.csv` | franchise branch owners, ok/good sites — send slowly |
| `clean_leads.csv` | every lead that passed validation, deduped |
| `site_checks.csv` | full audit detail per site (35 columns) |
| `needs_review.csv` | rejected rows + why |
| `domain_duplicates.csv` | extra contacts at an already-represented domain |
| `email_duplicates.csv` | exact duplicate email addresses |
| `unchecked_leads.csv` | clean leads with no audit result (only in `--sample` runs) |

Every input row ends up in exactly one of these — nothing is silently dropped.

## STEP 1 — what cleaning does

- **Column detection** from a large alias table, so Apollo / Sales Navigator /
  scraper exports all map onto the same canonical fields. Add new spellings to
  `HEADER_ALIASES` in `common.py`, or override per-run with `--map`.
- **Email**: lowercased, `Name <addr>` unwrapped, syntax validated. Rows are
  held back for missing/malformed addresses, placeholder domains
  (`example.com`), disposable domains, and unmonitored mailboxes (`noreply@`).
  Each address is typed `personal` / `personal_freemail` / `role`.
- **Verifier status** (MillionVerifier / ZeroBounce / NeverBounce) is read if
  present. `invalid`/`bad` goes to review; `risky`/catch-all is *flagged but
  kept* unless you pass `--drop-risky`.
- **Phone**: normalized to E.164 (`+442079460123`), extensions stripped, plus a
  human-readable `phone_pretty`.
- **Website**: forced to `https://`, host lowercased, trailing slashes,
  fragments and tracking params removed, IDNs punycoded.
- **Company / names**: whitespace and casing tidied, titles (`Mr`, `Dr`) and
  credentials (`RN`, `MSc`) stripped, `O'Brien` / `Wilson-Parker` cased right.
- **Dedupe** by email, then by registrable domain. Where several contacts share
  a domain the most senior and most complete one is kept — ranked by job title
  (Owner/Director > Registered Manager > HR > other), then verifier status,
  then personal-vs-role address, then field completeness.
- **Service focus** is extracted from the keywords column (dementia care,
  live-in care, supported living…) and feeds personalization.

Directory and social URLs (Facebook, CQC, carehome.co.uk, Yell…) are recognised
as "not their own website". Those leads are **kept and flagged**, not reviewed —
having no real site is the strongest Website Offer signal there is.

### Franchises vs care groups

Domain dedupe assumes one website means one company. That is wrong for
franchise networks, where dozens of independently-owned businesses sit behind
one head-office domain — collapsing them to a single lead throws away real
prospects. A large care group is the opposite case: fifty employees of one
company, which genuinely should collapse to one.

A domain is treated as a franchise network when it has at least four contacts
**and** either two or more owner-titled contacts (one company has one owner; a
franchise network has many) or three or more distinct branch names once the
shared brand and generic sector words are stripped. The three-name threshold
exists because a single odd name is nearly always a spelling variant —
"Nurse Plus UK" and "Nurseplus UK" are one company, not two.

Inside a franchise network the pipeline keeps one contact per named branch plus
every owner-titled contact; ordinary staff still collapse. Those extra leads are
marked `is_franchise_branch=yes` and routed to the `_FRANCHISE` files.

Note that private registries such as `uk.com` are treated as public suffixes, so
`agency-a.uk.com` and `agency-b.uk.com` count as different companies.

## STEP 2 — how a site is scored

One request per homepage. Sites start at 100 and lose points:

| Signal | Penalty |
|---|---|
| No HTTPS at all, or a broken/expired certificate | −35 |
| No mobile viewport tag | −30 |
| Viewport present but not `width=device-width` | −15 |
| Low-end builder (GoDaddy, Weebly, Jimdo, IONOS, Yola, Google Sites) | −30 |
| Legacy platform (FrontPage, Dreamweaver, old Wix) | −30 |
| WordPress on an untouched default theme | −20 |
| WordPress older than 6.x in the page source | −10 |
| Plain unstyled HTML (no stylesheets at all) | −35 |
| 1990s markup (`<font>`, `<center>`, `bgcolor`, framesets) | −15 |
| Footer copyright 6+ / 3+ years stale | −25 / −15 |
| Homepage load over 8s / 5s / 3s | −20 / −12 / −5 |
| Fewer than 4 internal links (single-page template) | −12 |
| Thin homepage (<15KB and almost no CSS) | −10 |
| Almost no text content / no images | −10 / −5 |

Small bonuses for a modern platform, a current copyright year, and sub-1.5s
loads.

**Hard triggers.** Some faults force `bad` no matter the score, because the
prospect can see them for themselves in ten seconds: unreachable, 4xx/5xx,
parked/"coming soon", no HTTPS, broken SSL, no mobile viewport, low-end
builder, legacy platform, WordPress default theme, plain unstyled HTML, and
6+ year stale copyright. Which one fired is recorded in `hard_bad_triggers`.

Otherwise: **good** ≥ 75, **ok** 45–74, **bad** < 45. Every score comes with a
plain-English `website_reasons` column.

Page weight is measured from the HTML document and asset counts, not by
downloading images and scripts — that keeps a 2k-lead run to 2k requests.

## STEP 3 — Instantly import

Both segment files use these headers, which become `{{variables}}` in your
sequence:

```
first_name, last_name, email, company, website, personalization_notes,
phone, job_title, service_focus, website_quality, website_score,
website_reasons, website_issue, email_status, email_type, linkedin,
domain, lead_id
```

`website_issue` is the single most pitchable problem, already ranked — mobile
first, then SSL, then dead/parked, then platform, then staleness, then speed.
It's the one to drop into a subject line or first sentence.

Note: Instantly's *built-in* company field is `company_name`. These files use
`company` — either remap it during import or rename the header first.

Rows are sorted worst-score-first, so the leads with the most obvious problems
are at the top of Segment A.

## Tests

```bash
python3 tests/test_site_scoring.py    # analyser, scorer, async fetch + cache
python3 tests/test_segmentation.py    # normalizers, validation, franchises, segments
```

The scoring tests run against fixture pages (modern, stale WordPress, GoDaddy
builder, 1990s HTML, parked domain) and the fetch tests against a throwaway
local HTTP server, so the whole suite runs offline.

## Re-running on a new list

Point `--input` at the new file. If its column names aren't recognised, the run
logs which columns it mapped and which it ignored — add the new spellings to
`HEADER_ALIASES` in `common.py` or pass `--map`.

## Data protection

Lead files contain personal data. `.gitignore` excludes `data/`, `output*/` and
all spreadsheets — keep it that way, and don't commit lead files to the repo.
