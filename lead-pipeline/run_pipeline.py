"""End-to-end runner: clean -> check websites -> segment -> summary.

    python3 run_pipeline.py --input leads.xlsx --sample 50     # test run
    python3 run_pipeline.py --input leads.xlsx                 # full run
"""

from __future__ import annotations

import argparse
import asyncio
import collections
import csv
import json
import os
import random
import sys
from typing import Dict, List

from clean_leads import CLEAN_COLUMNS, clean
from common import log, preview_csv, write_csv
from segment_leads import segment


def _count(path: str) -> int:
    if not os.path.exists(path):
        return 0
    with open(path, "r", encoding="utf-8-sig", newline="") as fh:
        return max(0, sum(1 for _ in csv.reader(fh)) - 1)


def _column_counter(path: str, column: str, limit: int = 8):
    if not os.path.exists(path):
        return []
    counter: collections.Counter = collections.Counter()
    with open(path, "r", encoding="utf-8-sig", newline="") as fh:
        for row in csv.DictReader(fh):
            for value in (row.get(column) or "").split(";"):
                value = value.strip()
                if value:
                    counter[value] += 1
    return counter.most_common(limit)


def main() -> None:
    ap = argparse.ArgumentParser(description="Clean, audit and segment a lead list")
    ap.add_argument("--input", required=True)
    ap.add_argument("--outdir", default="output")
    ap.add_argument("--sheet", default=None)
    ap.add_argument("--sample", type=int, default=0,
                    help="only audit N randomly-sampled leads (test run)")
    ap.add_argument("--seed", type=int, default=42)
    ap.add_argument("--concurrency", type=int, default=30)
    ap.add_argument("--timeout", type=float, default=20.0)
    ap.add_argument("--max-per-domain", type=int, default=1)
    ap.add_argument("--drop-risky", action="store_true")
    ap.add_argument("--refresh", action="store_true")
    ap.add_argument("--map", default=None)
    ap.add_argument("--preview", type=int, default=10)
    args = ap.parse_args()

    os.makedirs(args.outdir, exist_ok=True)
    overrides = json.loads(args.map) if args.map else None

    # -------- STEP 1 --------
    clean_stats = clean(args.input, args.outdir, args.max_per_domain,
                        args.drop_risky, args.sheet, overrides)
    leads_path = clean_stats["paths"]["clean"]

    with open(leads_path, "r", encoding="utf-8-sig", newline="") as fh:
        leads = list(csv.DictReader(fh))

    audit_path = leads_path
    if args.sample and args.sample < len(leads):
        leads = random.Random(args.seed).sample(leads, args.sample)
        audit_path = os.path.join(args.outdir, "clean_leads_sample.csv")
        write_csv(audit_path, leads, CLEAN_COLUMNS)
        log(f"[run] SAMPLE MODE — auditing {len(leads)} of "
            f"{clean_stats['clean_leads']} leads")

    # -------- STEP 2 --------
    from check_sites import SITE_COLUMNS, run_checks
    results = asyncio.run(run_checks(leads, args.outdir, args.concurrency,
                                     args.timeout, args.refresh))
    checks_path = os.path.join(args.outdir, "site_checks.csv")
    write_csv(checks_path, results, SITE_COLUMNS)

    # -------- STEP 3 --------
    seg_stats = segment(audit_path, checks_path, args.outdir)

    # -------- STEP 4 --------
    quality = collections.Counter(r.get("website_quality", "") for r in results)
    audited = len(results)
    review_path = clean_stats["paths"]["review"]

    print()
    print("=" * 72)
    print("PIPELINE SUMMARY")
    print("=" * 72)
    print(f"Input file                       {os.path.basename(args.input)}")
    print(f"Rows read                        {clean_stats['input_rows']}")
    print("-" * 72)
    print("STEP 1 — cleaning")
    print(f"  Passed validation              {clean_stats['passed_validation']}")
    print(f"  Flagged -> needs_review.csv    {clean_stats['needs_review']}")
    print(f"  Removed: duplicate emails      {clean_stats['email_duplicates_removed']}")
    print(f"  Removed: duplicate domains     {clean_stats['domain_duplicates_removed']}"
          f"  (max {args.max_per_domain}/domain, kept in domain_duplicates.csv)")
    print(f"  Clean leads                    {clean_stats['clean_leads']}")
    print("-" * 72)
    print(f"STEP 2 — website audit ({audited} sites checked)")
    for label in ("good", "ok", "bad"):
        count = quality.get(label, 0)
        pct = (count / audited * 100) if audited else 0
        print(f"  {label:<4}                           {count:>5}  ({pct:.1f}%)")
    unreachable = sum(1 for r in results if not r.get("responded"))
    http_errors = sum(1 for r in results
                      if r.get("responded") and not r.get("reachable"))
    no_ssl = sum(1 for r in results if not r.get("ssl_valid"))
    no_vp = sum(1 for r in results if not r.get("has_viewport"))
    print(f"  dead / no response             {unreachable}")
    print(f"  responded with 4xx/5xx         {http_errors}")
    print(f"  no valid SSL                   {no_ssl}")
    print(f"  no mobile viewport tag         {no_vp}")
    print("-" * 72)
    print("STEP 3 — segments")
    print(f"  A  Website Offer  (bad)        {seg_stats['segment_a']:>5}"
          f"   -> {os.path.basename(seg_stats['paths']['segment_a'])}")
    print(f"  B  HR Automation  (ok/good)    {seg_stats['segment_b']:>5}"
          f"   -> {os.path.basename(seg_stats['paths']['segment_b'])}")
    if seg_stats["unchecked"]:
        print(f"  unchecked                      {seg_stats['unchecked']:>5}")
    print("-" * 72)
    print("Dropped / held back")
    print(f"  needs_review.csv               {_count(review_path)}")
    print(f"  email_duplicates.csv           {_count(clean_stats['paths']['email_dupes'])}")
    print(f"  domain_duplicates.csv          {_count(clean_stats['paths']['domain_dupes'])}")

    top_reasons = _column_counter(seg_stats["paths"]["segment_a"], "website_reasons")
    if top_reasons:
        print("-" * 72)
        print("Top reasons a site scored 'bad'")
        for reason, count in top_reasons:
            print(f"  {count:>5}  {reason[:60]}")

    review_reasons = _column_counter(review_path, "review_reasons")
    if review_reasons:
        print("-" * 72)
        print("Top needs_review reasons")
        for reason, count in review_reasons:
            print(f"  {count:>5}  {reason[:60]}")

    if args.preview:
        for label, key in (("SEGMENT A — Website Offer", "segment_a"),
                           ("SEGMENT B — HR Automation Offer", "segment_b")):
            print()
            print("=" * 72)
            print(f"{label}  (first {args.preview} rows)")
            print("=" * 72)
            path = seg_stats["paths"][key]
            print(preview_csv(path, limit=args.preview))

    print()
    print(f"All output written to: {os.path.abspath(args.outdir)}")


if __name__ == "__main__":
    main()
