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
        from notifications.telegram_sender import send_daily_summary
        log.info("[Webhook] Running on-demand daily summary...")
        ok = send_daily_summary()
        log.info(f"[Webhook] On-demand summary {'sent ✅' if ok else 'failed ❌'}")
    except Exception as e:
        log.error(f"[Webhook] Summary error: {e}", exc_info=True)


def _send_ticker_alert(ticker: str):
    try:
        from scoring.signal_scorer import score_ticker
        from notifications.telegram_sender import send_alert, send_message
        result = score_ticker(ticker)
        if result:
            send_alert(ticker, result["conviction_score"], result["risk_tier"], result.get("reasoning", ""))
            log.info(f"[Webhook] Alert sent for {ticker}: {result['conviction_score']:.1f}/10")
        else:
            send_message(f"⚡ StockPulse: No recent signals for <b>{ticker}</b>. Check back after the next scan (every 30 min).")
    except Exception as e:
        log.error(f"[Webhook] Alert error for {ticker}: {e}", exc_info=True)
        try:
            from notifications.telegram_sender import send_message
            send_message(f"StockPulse: Could not score {ticker} right now. Try again in a few minutes.")
        except Exception:
            pass


def _get_status():
    try:
        from database.db import execute
        from datetime import datetime
        from datetime import date
        scores_today = execute("SELECT COUNT(*) as cnt FROM daily_scores WHERE date = ?", (date.today().isoformat(),))
        cnt = scores_today[0]["cnt"] if scores_today else 0
        now = datetime.now().strftime("%H:%M UTC")
        return f"StockPulse ✅ running — {cnt} tickers scored today — last check {now}"
    except Exception as e:
        return f"StockPulse: status check error ({e})"


def _handle_telegram_command(text: str):
    """Dispatch an incoming Telegram bot message to the right action."""
    cmd = text.strip().lower()
    if cmd in ("summary", "update", "digest", "morning", "today", "top", "picks"):
        threading.Thread(target=_send_summary, daemon=True).start()
    elif cmd.startswith("alert "):
        ticker = cmd.split(" ", 1)[1].upper().strip()
        threading.Thread(target=_send_ticker_alert, args=(ticker,), daemon=True).start()
    elif cmd in ("status", "ping", "check"):
        try:
            from notifications.telegram_sender import send_message
            send_message(_get_status())
        except Exception:
            pass
    else:
        try:
            from notifications.telegram_sender import send_message
            send_message(
                "<b>StockPulse commands:</b>\n"
                "• <b>summary</b> — full market update now\n"
                "• <b>alert NVDA</b> — instant analysis for any ticker\n"
                "• <b>status</b> — system health\n"
                "• <b>top</b> — top picks today"
            )
        except Exception:
            pass


def _telegram_poll_loop():
    """Long-poll Telegram for incoming messages and dispatch commands."""
    import requests as req
    token = os.getenv("TELEGRAM_BOT_TOKEN", "")
    if not token:
        log.warning("[Telegram] TELEGRAM_BOT_TOKEN not set — bot polling disabled")
        return

    api   = f"https://api.telegram.org/bot{token}"
    offset = None
    log.info("[Telegram] Bot polling started — message your bot to control StockPulse")

    while True:
        try:
            params = {"timeout": 30, "allowed_updates": ["message"]}
            if offset:
                params["offset"] = offset
            resp   = req.get(f"{api}/getUpdates", params=params, timeout=40).json()
            for update in resp.get("result", []):
                offset = update["update_id"] + 1
                try:
                    text = update["message"]["text"]
                    log.info(f"[Telegram] Command: '{text}'")
                    _handle_telegram_command(text)
                except KeyError:
                    pass
        except Exception as e:
            log.warning(f"[Telegram] Poll error: {e}")
            import time; time.sleep(5)


def start_webhook_server():
    """Start Flask HTTP server + Telegram polling, both in background threads."""
    # Telegram long-polling thread
    tg_thread = threading.Thread(target=_telegram_poll_loop, daemon=True, name="telegram-poll")
    tg_thread.start()

    # Flask HTTP server (for health checks and any future webhooks)
    port = int(os.getenv("PORT", "8080"))
    log.info(f"[Webhook] Starting HTTP server on port {port}...")
    app.run(host="0.0.0.0", port=port, threaded=True, use_reloader=False)


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
    start_webhook_server()
