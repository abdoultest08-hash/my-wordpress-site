"""
StockPulse main pipeline.

One full run:
  1. Collect signals (Reddit + News)
  2. Detect thematic cascades
  3. Score all tickers
  4. Send instant SMS alerts for any ticker >= conviction threshold
"""

import os
import sys
import traceback
from datetime import datetime, timezone
from pathlib import Path
from dotenv import load_dotenv

_BASE = Path(__file__).parent
load_dotenv(_BASE / ".env")
os.chdir(_BASE)
sys.path.insert(0, str(_BASE))

from collectors.reddit_collector import collect as collect_reddit
from collectors.news_collector   import collect as collect_news
from scoring.thematic_cascade    import detect_cascades
from scoring.signal_scorer       import score_all
from notifications.sms_sender    import send_alert_sms
from database.db                 import execute


def run_pipeline(send_alerts: bool = True) -> dict:
    started_at = datetime.now(timezone.utc)
    print(f"\n{'='*60}")
    print(f"StockPulse pipeline — {started_at.strftime('%Y-%m-%d %H:%M UTC')}")
    print(f"{'='*60}")

    summary = {
        "started_at":     started_at.isoformat(),
        "reddit_signals": 0,
        "news_signals":   0,
        "cascades":       0,
        "tickers_scored": 0,
        "alerts_sent":    0,
        "errors":         [],
    }

    print("\n[1/4] Collecting signals...")
    try:
        summary["reddit_signals"] = collect_reddit()
    except Exception:
        summary["errors"].append(f"reddit: {traceback.format_exc()}")

    try:
        summary["news_signals"] = collect_news()
    except Exception:
        summary["errors"].append(f"news: {traceback.format_exc()}")

    print(f"  Total new signals: {summary['reddit_signals'] + summary['news_signals']}")

    print("\n[2/4] Detecting thematic cascades...")
    try:
        summary["cascades"] = detect_cascades()
        print(f"  {summary['cascades']} cascade event(s) detected")
    except Exception:
        summary["errors"].append(f"cascade: {traceback.format_exc()}")

    print("\n[3/4] Scoring tickers...")
    results = []
    try:
        results = score_all()
        summary["tickers_scored"] = len(results)
        print(f"  {len(results)} tickers scored")
        for r in results[:5]:
            print(f"    {r['ticker']:<6} {r['conviction_score']:>4.1f}/10  [{r['risk_tier']}]")
        if len(results) > 5:
            print(f"    ... and {len(results)-5} more")
    except Exception:
        summary["errors"].append(f"scorer: {traceback.format_exc()}")

    print("\n[4/4] Checking alert thresholds...")
    if send_alerts and results:
        threshold = float(os.getenv("CONVICTION_ALERT_THRESHOLD", "8.0"))
        for r in results:
            if r["conviction_score"] < threshold:
                continue
            ticker, score, risk_tier, reasoning = (
                r["ticker"], r["conviction_score"], r["risk_tier"], r.get("reasoning", "")
            )
            recent = execute("""
                SELECT id FROM alerts
                WHERE ticker = ?
                  AND alert_type = 'instant'
                  AND sent_at >= datetime('now', '-6 hours')
            """, (ticker,))
            if recent:
                print(f"  {ticker} already alerted in last 6h — skipping")
                continue
            print(f"  ⚡ ALERT: {ticker} scored {score:.1f}/10 [{risk_tier}]")
            if send_alert_sms(ticker, score, risk_tier, reasoning):
                summary["alerts_sent"] += 1
    else:
        print("  Alerts disabled" if not send_alerts else "  No scores available")

    elapsed = (datetime.now(timezone.utc) - started_at).total_seconds()
    print(f"\n{'='*60}")
    print(f"Pipeline complete in {elapsed:.1f}s — scored:{summary['tickers_scored']} alerts:{summary['alerts_sent']}")
    print(f"{'='*60}\n")
    return summary


if __name__ == "__main__":
    run_pipeline(send_alerts="--no-alerts" not in sys.argv)
