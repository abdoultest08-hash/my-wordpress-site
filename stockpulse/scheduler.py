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
from notifications.sms_sender import send_daily_sms_summary

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

    job_pipeline()

    schedule.every(scan_interval).minutes.do(job_pipeline)
    schedule.every().day.at(digest_time).do(job_daily_digest)

    while True:
        schedule.run_pending()
        time.sleep(60)


if __name__ == "__main__":
    main()
