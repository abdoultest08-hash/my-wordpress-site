"""
SMS notifications — instant alerts via Twilio when conviction score >= 8.

Sign up at twilio.com (free trial gives ~$15 credit, enough for ~200 SMS).
Set in .env: TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM_NUMBER, SMS_TO_NUMBER
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
            raise ValueError("TWILIO_ACCOUNT_SID or TWILIO_AUTH_TOKEN not set")
        return Client(sid, token)
    except ImportError:
        raise ImportError("Run: pip install twilio")


def send_alert_sms(ticker: str, score: float, risk_tier: str, reasoning: str) -> bool:
    """
    Send an instant SMS alert. Called when conviction score >= threshold.
    Returns True on success.
    """
    from_number = os.getenv("TWILIO_FROM_NUMBER", "")
    to_number   = os.getenv("SMS_TO_NUMBER", "")

    if not from_number or not to_number:
        print("[SMS] TWILIO_FROM_NUMBER or SMS_TO_NUMBER not set — skipping")
        return False

    # Keep SMS concise — it's a push notification, not a report
    short_reason = reasoning[:120] if reasoning else ""
    body = (
        f"⚡ StockPulse Alert\n"
        f"{ticker} — {score:.1f}/10 [{risk_tier} Risk]\n"
        f"{short_reason}\n"
        f"Check your email for full details."
    )

    try:
        client = _get_twilio()
        message = client.messages.create(
            body=body,
            from_=from_number,
            to=to_number,
        )

        success = message.status in ("queued", "sent", "delivered")

        # Log to alerts table
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

        status = f"✅ sent (SID: {message.sid})" if success else f"❌ failed ({message.status})"
        print(f"[SMS] {ticker} alert → {to_number} {status}")
        return success

    except Exception as e:
        print(f"[SMS] Error: {e}")
        return False
