"""
Twilio incoming SMS webhook.

Twilio sends a POST to this server when someone texts the StockPulse number.
Supported commands (case-insensitive):
  summary / update / digest   → send the 3-part daily SMS summary now
  alert TICKER                → send an instant alert for that ticker
  status                      → quick reply with system status
  help / anything else        → reply with command list

Railway setup:
  1. This server starts automatically alongside the scheduler.
  2. Set your Twilio phone number's "A message comes in" webhook to:
       https://<your-railway-url>/sms
     (Method: HTTP POST)
"""

import os
import sys
import threading
import logging
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))

from flask import Flask, request, Response

log = logging.getLogger("stockpulse.webhook")

app = Flask(__name__)


def _twiml(msg: str) -> Response:
    """Return a TwiML response that sends msg back to the sender."""
    body = f"""<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>{msg}</Message></Response>"""
    return Response(body, mimetype="application/xml")


@app.route("/health", methods=["GET"])
def health():
    return {"status": "ok", "service": "stockpulse"}, 200


@app.route("/sms", methods=["POST"])
def sms_reply():
    body   = (request.form.get("Body") or "").strip().lower()
    sender = request.form.get("From", "unknown")
    log.info(f"[Webhook] SMS from {sender}: '{body}'")

    if body in ("summary", "update", "digest", "morning", "today"):
        log.info("[Webhook] Triggering daily summary on demand...")
        threading.Thread(target=_send_summary, daemon=True).start()
        return _twiml("Generating your StockPulse summary now — you'll receive 3 messages in ~30 seconds.")

    if body.startswith("alert "):
        ticker = body.split(" ", 1)[1].upper().strip()
        log.info(f"[Webhook] On-demand alert requested for {ticker}")
        threading.Thread(target=_send_ticker_alert, args=(ticker,), daemon=True).start()
        return _twiml(f"Running analysis for {ticker}... alert coming shortly.")

    if body in ("status", "ping", "check"):
        return _twiml(_get_status())

    if body in ("top", "picks", "watchlist"):
        threading.Thread(target=_send_summary, daemon=True).start()
        return _twiml("Pulling top picks for you now — 3 messages incoming.")

    # Default help
    return _twiml(
        "StockPulse commands:\n"
        "• summary — 3-part market update\n"
        "• alert NVDA — instant analysis on any ticker\n"
        "• status — system health\n"
        "• top — top picks\n"
        "Reply with any of the above to get started."
    )


def _send_summary():
    try:
        from notifications.sms_sender import send_daily_sms_summary
        log.info("[Webhook] Running on-demand daily summary...")
        ok = send_daily_sms_summary()
        log.info(f"[Webhook] On-demand summary {'sent ✅' if ok else 'failed ❌'}")
    except Exception as e:
        log.error(f"[Webhook] Summary error: {e}", exc_info=True)


def _send_ticker_alert(ticker: str):
    try:
        from scoring.signal_scorer import score_ticker
        from notifications.sms_sender import send_alert_sms
        result = score_ticker(ticker)
        if result:
            send_alert_sms(ticker, result["conviction_score"], result["risk_tier"], result.get("reasoning", ""))
            log.info(f"[Webhook] Alert sent for {ticker}: {result['conviction_score']:.1f}/10")
        else:
            # Fallback — send with neutral score
            from notifications.sms_sender import _send_sms
            _send_sms(f"⚡ StockPulse: No recent signals found for {ticker}. Check back after the next scan (every 30 min).")
    except Exception as e:
        log.error(f"[Webhook] Alert error for {ticker}: {e}", exc_info=True)
        try:
            from notifications.sms_sender import _send_sms
            _send_sms(f"StockPulse: Could not score {ticker} right now. Try again in a few minutes.")
        except Exception:
            pass


def _get_status():
    try:
        from database.db import execute
        from datetime import datetime
        scores_today = execute("SELECT COUNT(*) as cnt FROM daily_scores WHERE date = date('now')")
        cnt = scores_today[0]["cnt"] if scores_today else 0
        now = datetime.now().strftime("%H:%M UTC")
        return f"StockPulse ✅ running — {cnt} tickers scored today — last check {now}"
    except Exception as e:
        return f"StockPulse: status check error ({e})"


def start_webhook_server():
    """Start Flask in a background daemon thread."""
    port = int(os.getenv("PORT", "8080"))
    log.info(f"[Webhook] Starting SMS webhook on port {port}...")
    # Use threaded=True so each request runs in its own thread
    app.run(host="0.0.0.0", port=port, threaded=True, use_reloader=False)


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
    start_webhook_server()
