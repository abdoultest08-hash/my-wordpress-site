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

import threading
import schedule

from main import run_pipeline
from notifications.telegram_sender import send_daily_summary, send_alert

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
        results = score_all()
        match = next((r for r in results if r["ticker"] == ticker), None)
        if match:
            score     = match["conviction_score"]
            risk_tier = match["risk_tier"]
            reasoning = match.get("reasoning", "")
            log.info(f"TEST: {ticker} scored {score:.1f}/10 [{risk_tier}] — sending Telegram alert")
            send_alert(ticker, score, risk_tier, reasoning)
        else:
            log.warning(f"TEST: {ticker} not found in scored results — sending preview")
            send_alert(ticker, 7.5, "Medium",
                f"Test alert for {ticker}. Live scoring will run every 30 minutes.")
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
    log.info("Sending daily Telegram summary...")
    try:
        ok = send_daily_summary()
        log.info(f"Daily summary {'sent ✅' if ok else 'failed ❌'}")
    except Exception as e:
        log.error(f"Daily summary error: {e}", exc_info=True)


def _start_webhook():
    """Start the Telegram bot polling + HTTP webhook in a background thread."""
    try:
        from webhook import start_webhook_server
        t = threading.Thread(target=start_webhook_server, daemon=True, name="webhook")
        t.start()
        log.info("[Webhook] Server started")
    except Exception as e:
        log.error(f"[Webhook] Failed to start: {e}")


def main():
    digest_time   = os.getenv("DIGEST_TIME", "07:00")
    scan_interval = int(os.getenv("SCAN_INTERVAL_MINUTES", "30"))

    log.info("=" * 50)
    log.info("StockPulse scheduler starting")
    log.info(f"  Scan every {scan_interval} minutes")
    log.info(f"  Daily digest at {digest_time}")
    log.info("=" * 50)

    # Warn loudly about missing keys — prices will fail without Finnhub
    if not os.getenv("FINNHUB_API_KEY"):
        log.warning("⚠️  FINNHUB_API_KEY not set — NO LIVE PRICES will be available!")
        log.warning("   Get a free key at finnhub.io → add FINNHUB_API_KEY to Railway Variables")
    if not os.getenv("TELEGRAM_CHAT_ID"):
        log.warning("⚠️  TELEGRAM_CHAT_ID not set — send any message to your bot to activate it")

    _start_webhook()

    test_ticker = os.getenv("TEST_TICKER", "").upper().strip()
    if test_ticker:
        log.info(f"TEST_TICKER={test_ticker} — running forced analysis...")
        _run_test_alert(test_ticker)

    if os.getenv("FORCE_DAILY_SUMMARY", "").lower() == "true":
        log.info("FORCE_DAILY_SUMMARY=true — running pipeline then sending daily summary now...")
        job_pipeline()
        job_daily_digest()
    else:
        job_pipeline()

    schedule.every(scan_interval).minutes.do(job_pipeline)
    schedule.every().day.at(digest_time).do(job_daily_digest)

    while True:
        schedule.run_pending()
        time.sleep(60)


if __name__ == "__main__":
    main()
