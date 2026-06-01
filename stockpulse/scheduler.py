""" 
StockPulse 24/7 scheduler.

Schedule:
  - Every 30 minutes : full pipeline (collect → cascade → score → SMS alerts)
  - Daily at 07:00   : send daily SMS summary
  - On startup       : run pipeline immediately
"""

import os
import sys
import time
import logging
from pathlib import Path
from dotenv import load_dotenv

load_dotenv(Path(__file__).parent / ".env")
sys.path.insert(0, str(Path(__file__).parent))

import schedule

from main import run_pipeline
from notifications.sms_sender import send_daily_sms_summary, send_alert_sms

_LOG_DIR = Path(__file__).parent / "logs"
_LOG_DIR.mkdir(exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s  %(levelname)s  %(message)s",
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler(_LOG_DIR / "scheduler.log"),
    ]
)
log = logging.getLogger("stockpulse")


def _run_test_alert(ticker: str):
    try:
        from scoring.signal_scorer import score_all
        from database.mock_data import insert_mock_signals
        insert_mock_signals()
        results = score_all()
        match = next((r for r in results if r["ticker"] == ticker), None)
        if match:
            score     = match["conviction_score"]
            risk_tier = match["risk_tier"]
            reasoning = match.get("reasoning", "")
            log.info(f"TEST: {ticker} scored {score:.1f}/10 [{risk_tier}] — sending SMS")
            send_alert_sms(ticker, score, risk_tier, reasoning)
        else:
            log.warning(f"TEST: {ticker} not found in scored results — sending preview SMS")
            send_alert_sms(
                ticker, 7.5, "Medium",
                f"Test alert for {ticker}. Live scoring will run every 30 minutes."
            )
    except Exception as e:
        log.error(f"Test alert error: {e}", exc_info=True)


def job_pipeline():
    log.info("Scheduled pipeline run starting...")
    try:
        summary = run_pipeline(send_alerts=True)
        log.info(f"Pipeline done — scored:{summary['tickers_scored']} alerts:{summary['alerts_sent']}")
    except Exception as e:
        log.error(f"Pipeline error: {e}", exc_info=True)


def job_daily_digest():
    log.info("Sending daily SMS summary...")
    try:
        ok = send_daily_sms_summary()
        log.info(f"Daily SMS {'sent' if ok else 'failed'}")
    except Exception as e:
        log.error(f"Daily SMS error: {e}", exc_info=True)


def main():
    digest_time   = os.getenv("DIGEST_TIME", "07:00")
    scan_interval = int(os.getenv("SCAN_INTERVAL_MINUTES", "30"))

    log.info("=" * 50)
    log.info("StockPulse scheduler starting")
    log.info(f"  Scan every {scan_interval} minutes")
    log.info(f"  Daily SMS at {digest_time}")
    log.info("=" * 50)

    test_ticker = os.getenv("TEST_TICKER", "").upper().strip()
    if test_ticker:
        log.info(f"TEST_TICKER={test_ticker} — running forced analysis and SMS...")
        _run_test_alert(test_ticker)

    job_pipeline()

    schedule.every(scan_interval).minutes.do(job_pipeline)
    schedule.every().day.at(digest_time).do(job_daily_digest)

    while True:
        schedule.run_pending()
        time.sleep(60)


if __name__ == "__main__":
    main()
