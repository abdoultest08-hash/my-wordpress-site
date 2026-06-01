"""
SMS notifications — instant alerts and daily 7am summary via Twilio.

Set in Railway Variables: TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN,
TWILIO_FROM_NUMBER, SMS_TO_NUMBER
"""

import json
import os
from datetime import datetime, timezone
from pathlib import Path
import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import insert


def _get_twilio():
    try:
        from twilio.rest import Client
        sid   = os.getenv("TWILIO_ACCOUNT_SID", "")
        token = os.getenv("TWILIO_AUTH_TOKEN", "")
        if not sid or not token:
            raise ValueError("TWILIO credentials not set")
        return Client(sid, token)
    except ImportError:
        raise ImportError("Run: pip install twilio")


def send_alert_sms(ticker: str, score: float, risk_tier: str, reasoning: str) -> bool:
    """Send an instant SMS when conviction score >= threshold."""
    from_number = os.getenv("TWILIO_FROM_NUMBER", "")
    to_number   = os.getenv("SMS_TO_NUMBER", "")

    if not from_number or not to_number:
        print("[SMS] Twilio numbers not set — skipping")
        return False

    short_reason = reasoning[:120] if reasoning else ""
    body = (
        f"⚡ StockPulse Alert\n"
        f"{ticker} — {score:.1f}/10 [{risk_tier} Risk]\n"
        f"{short_reason}"
    )

    try:
        client = _get_twilio()
        message = client.messages.create(body=body, from_=from_number, to=to_number)
        success = message.status in ("queued", "sent", "delivered")

        try:
            insert("alerts", {
                "ticker":           ticker,
                "alert_type":       "instant",
                "conviction_score": score,
                "risk_tier":        risk_tier,
                "trigger_reason":   reasoning or "High conviction score",
                "channels_sent":    json.dumps(["sms"]),
                "email_sent":       0,
                "sms_sent":         1 if success else 0,
                "sent_at":          datetime.now(timezone.utc).isoformat(),
            })
        except Exception:
            pass

        print(f"[SMS] {ticker} alert → {to_number} {'✅' if success else '❌'}")
        return success

    except Exception as e:
        print(f"[SMS] Error: {e}")
        return False


def send_daily_sms_summary() -> bool:
    """Send a concise 7am SMS with today's top stocks. No email needed."""
    from database.db import execute

    from_number = os.getenv("TWILIO_FROM_NUMBER", "")
    to_number   = os.getenv("SMS_TO_NUMBER", "")

    if not from_number or not to_number:
        print("[SMS] Twilio numbers not set — skipping daily summary")
        return False

    scores = execute("""
        SELECT ticker, conviction_score, risk_tier
        FROM daily_scores
        WHERE date = date('now')
        ORDER BY conviction_score DESC
        LIMIT 10
    """)

    if not scores:
        print("[SMS] No scores for today — skipping")
        return False

    date_str = datetime.now().strftime("%b %-d")
    lines = [f"📈 StockPulse {date_str}"]

    alerts = [s for s in scores if s["conviction_score"] >= 8.0]
    if alerts:
        lines.append("\n⚡ HIGH CONVICTION:")
        for s in alerts:
            lines.append(f"  {s['ticker']} {s['conviction_score']:.1f}/10 [{s['risk_tier']}]")

    lines.append("\n📊 Top picks:")
    for s in scores[:5]:
        bar = "▲" if s["conviction_score"] >= 7 else ("→" if s["conviction_score"] >= 5 else "▼")
        lines.append(f"  {bar} {s['ticker']} {s['conviction_score']:.1f}/10")

    body = "\n".join(lines)

    try:
        client = _get_twilio()
        message = client.messages.create(body=body, from_=from_number, to=to_number)
        success = message.status in ("queued", "sent", "delivered")
        print(f"[SMS] Daily summary → {to_number} {'✅' if success else '❌'}")
        return success
    except Exception as e:
        print(f"[SMS] Daily summary error: {e}")
        return False
