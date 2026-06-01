"""
StockPulse main pipeline.

One full run:
  1. Collect signals (Reddit + News)
  2. Detect thematic cascades
  3. Score all tickers
  4. Send instant alerts for any ticker >= conviction threshold
  5. Log results

The scheduler calls this every 30 minutes.
The daily digest is sent separately by the scheduler at 7am.
"""

import os
import sys
import traceback
from datetime import datetime, timezone
from pathlib import Path
from dotenv import load_dotenv

_BASE = Path(__file__).parent
load_dotenv(_BASE / ".env")

# Ensure imports resolve correctly regardless of where the script is invoked from
os.chdir(_BASE)
sys.path.insert(0, str(_BASE))

from collectors.reddit_collector import collect as collect_reddit
from collectors.news_collector   import collect as collect_news
from scoring.thematic_cascade    import detect_cascades
from scoring.signal_scorer       import score_all
from notifications.email_sender  import send_instant_alert
from notifications.sms_sender    import send_alert_sms
from database.db                 import execute


def run_pipeline(send_alerts: bool = True) -> dict:
    """
    Run the full StockPulse pipeline once.
    Returns a summary dict of what happened.
    """
    started_at = datetime.now(timezone.utc)
    print(f"\n{'='*60}")
    print(f"StockPulse pipeline — {started_at.strftime('%Y-%m-%d %H:%M UTC')}")
    print(f"{'='*60}")

    summary = {
        "started_at":       started_at.isoformat(),
        "reddit_signals":   0,
        "news_signals":     0,
        "cascades":         0,
        "tickers_scored":   0,
        "alerts_sent":      0,
        "errors":           [],
    }

    # ── Step 1: Collect signals ─────────────────────────────────────────────
    print("\n[1/4] Collecting signals...")
    try:
        summary["reddit_signals"] = collect_reddit()
    except Exception as e:
        print(f"  Reddit collector error: {e}")
        summary["errors"].append(f"reddit: {traceback.format_exc()}")

    try:
        summary["news_signals"] = collect_news()
    except Exception as e:
        print(f"  News collector error: {e}")
        summary["errors"].append(f"news: {traceback.format_exc()}")

    total_signals = summary["reddit_signals"] + summary["news_signals"]
    print(f"  Total new signals: {total_signals}")

    # ── Step 2: Detect thematic cascades ───────────────────────────────────
    print("\n[2/4] Detecting thematic cascades...")
    try:
        summary["cascades"] = detect_cascades()
        print(f"  {summary['cascades']} cascade event(s) detected")
    except Exception as e:
        print(f"  Cascade detector error: {e}")
        summary["errors"].append(f"cascade: {traceback.format_exc()}")

    # ── Step 3: Score all tickers ───────────────────────────────────────────
    print("\n[3/4] Scoring tickers...")
    try:
        results = score_all()
        summary["tickers_scored"] = len(results)
        print(f"  {len(results)} tickers scored")
        for r in results[:5]:  # print top 5
            print(f"    {r['ticker']:<6} {r['conviction_score']:>4.1f}/10  [{r['risk_tier']}]")
        if len(results) > 5:
            print(f"    ... and {len(results)-5} more")
    except Exception as e:
        print(f"  Scorer error: {e}")
        summary["errors"].append(f"scorer: {traceback.format_exc()}")
        results = []

    # ── Step 4: Send instant alerts ─────────────────────────────────────────
    print("\n[4/4] Checking alert thresholds...")
    if send_alerts and results:
        threshold = float(os.getenv("CONVICTION_ALERT_THRESHOLD", "8.0"))

        for r in results:
            if r["conviction_score"] < threshold:
                continue

            ticker    = r["ticker"]
            score     = r["conviction_score"]
            risk_tier = r["risk_tier"]
            reasoning = r.get("reasoning", "")

            # Don't re-alert for the same ticker within 6 hours
            recent_alert = execute("""
                SELECT id FROM alerts
                WHERE ticker = ?
                  AND alert_type = 'instant'
                  AND sent_at >= datetime('now', '-6 hours')
            """, (ticker,))

            if recent_alert:
                print(f"  {ticker} already alerted in last 6h — skipping")
                continue

            print(f"  ⚡ ALERT: {ticker} scored {score:.1f}/10 [{risk_tier}]")

            email_ok = send_instant_alert(ticker, score, risk_tier, reasoning)
            sms_ok   = send_alert_sms(ticker, score, risk_tier, reasoning)

            if email_ok or sms_ok:
                summary["alerts_sent"] += 1
    else:
        print("  Alerts disabled for this run" if not send_alerts else "  No scores available")

    # ── Done ─────────────────────────────────────────────────────────────────
    elapsed = (datetime.now(timezone.utc) - started_at).total_seconds()
    print(f"\n{'='*60}")
    print(f"Pipeline complete in {elapsed:.1f}s")
    print(f"  Signals collected : {total_signals}")
    print(f"  Cascades detected : {summary['cascades']}")
    print(f"  Tickers scored    : {summary['tickers_scored']}")
    print(f"  Alerts sent       : {summary['alerts_sent']}")
    if summary["errors"]:
        print(f"  Errors            : {', '.join(summary['errors'])}")
    print(f"{'='*60}\n")

    return summary


if __name__ == "__main__":
    run_pipeline(send_alerts="--no-alerts" not in sys.argv)
