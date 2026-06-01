"""
StockPulse 24/7 scheduler.

Runs on Railway as the main process.

Schedule:
  - Every 30 minutes : full pipeline (collect → cascade → score → alerts)
  - Daily at 07:00   : send digest email
  - On startup       : run pipeline immediately so there's no delay
"""

import os
import sys
import time
import logging
from datetime import datetime, timezone
from pathlib import Path
from dotenv import load_dotenv

load_dotenv(Path(__file__).parent / ".env")
sys.path.insert(0, str(Path(__file__).parent))

import schedule

from main import run_pipeline
from notifications.email_sender import send_daily_digest

# ---------------------------------------------------------------------------
# Logging — writes to stdout (Railway captures this) and to logs/
# ---------------------------------------------------------------------------

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


# ---------------------------------------------------------------------------
# Scheduled jobs
# ---------------------------------------------------------------------------

def job_pipeline():
    log.info("Scheduled pipeline run starting...")
    try:
        summary = run_pipeline(send_alerts=True)
        log.info(
            f"Pipeline done — signals:{summary['reddit_signals']+summary['news_signals']} "
            f"cascades:{summary['cascades']} scored:{summary['tickers_scored']} "
            f"alerts:{summary['alerts_sent']}"
        )
    except Exception as e:
        log.error(f"Pipeline error: {e}", exc_info=True)


def job_daily_digest():
    log.info("Sending daily digest email...")
    try:
        ok = send_daily_digest()
        log.info(f"Daily digest {'sent' if ok else 'failed'}")
    except Exception as e:
        log.error(f"Digest error: {e}", exc_info=True)


# ---------------------------------------------------------------------------
# Main entry point
# ---------------------------------------------------------------------------

def main():
    digest_time = os.getenv("DIGEST_TIME", "07:00")
    scan_interval = int(os.getenv("SCAN_INTERVAL_MINUTES", "30"))
    timezone_name = os.getenv("DIGEST_TIMEZONE", "America/New_York")

    log.info("=" * 50)
    log.info("StockPulse scheduler starting")
    log.info(f"  Scan interval : every {scan_interval} minutes")
    log.info(f"  Daily digest  : {digest_time} ({timezone_name})")
    log.info("=" * 50)

    # Run immediately on startup
    log.info("Running startup pipeline...")
    job_pipeline()

    # Schedule recurring runs
    schedule.every(scan_interval).minutes.do(job_pipeline)
    schedule.every().day.at(digest_time).do(job_daily_digest)

    log.info(f"Scheduler running — next pipeline in {scan_interval} minutes")

    while True:
        schedule.run_pending()
        time.sleep(60)   # check every minute


if __name__ == "__main__":
    main()
