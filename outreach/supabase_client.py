"""
supabase_client.py
Lightweight Supabase REST client for the outreach pipeline.
Updates lead status, logs emails sent, and logs replies.
"""
import os
import json
import requests
from datetime import date
from pathlib import Path

SUPABASE_URL = os.environ.get("SUPABASE_URL", "")
SUPABASE_KEY = os.environ.get("SUPABASE_KEY", "")

def _headers():
    return {
        "apikey":        SUPABASE_KEY,
        "Authorization": f"Bearer {SUPABASE_KEY}",
        "Content-Type":  "application/json",
        "Prefer":        "return=representation",
    }

def _get(table, params=""):
    r = requests.get(f"{SUPABASE_URL}/rest/v1/{table}?{params}", headers=_headers())
    r.raise_for_status()
    return r.json()

def _post(table, data):
    r = requests.post(f"{SUPABASE_URL}/rest/v1/{table}", headers=_headers(), json=data)
    r.raise_for_status()
    return r.json()

def _patch(table, match_col, match_val, data):
    h = {**_headers(), "Prefer": "return=minimal"}
    r = requests.patch(
        f"{SUPABASE_URL}/rest/v1/{table}?{match_col}=eq.{match_val}",
        headers=h, json=data
    )
    r.raise_for_status()


# ── Lead operations ───────────────────────────────────────────────────────────

def upsert_lead(lead: dict) -> str | None:
    """Insert or update a lead by email. Returns the lead ID."""
    payload = {
        "business_name": lead.get("business_name"),
        "owner_name":    lead.get("owner_name"),
        "industry":      lead.get("industry"),
        "city":          lead.get("city"),
        "state":         lead.get("state"),
        "email":         lead.get("email"),
        "phone":         lead.get("phone"),
        "notes":         lead.get("notes"),
        "logo_url":      lead.get("logo_url"),
        "gmb_url":       lead.get("gmb_url"),
        "status":        "pending",
    }
    h = {**_headers(), "Prefer": "resolution=merge-duplicates,return=representation"}
    r = requests.post(f"{SUPABASE_URL}/rest/v1/leads", headers=h, json=payload)
    r.raise_for_status()
    result = r.json()
    return result[0]["id"] if result else None


def mark_site_generated(lead_id: str, screenshot_path: str):
    _patch("leads", "id", lead_id, {
        "status":          "site_generated",
        "screenshot_path": screenshot_path,
    })


def mark_email_sent(lead_id: str, from_account: str, subject: str, gmail_id: str):
    from datetime import datetime, timezone
    _patch("leads", "id", lead_id, {
        "status":          "email_sent",
        "email_sent_at":   datetime.now(timezone.utc).isoformat(),
        "email_sent_from": from_account,
    })
    _post("email_log", {
        "lead_id":          lead_id,
        "from_account":     from_account,
        "subject":          subject,
        "gmail_message_id": gmail_id,
        "status":           "sent",
    })


def log_reply(lead_id: str, thread_id: str, snippet: str, is_positive: bool):
    from datetime import datetime, timezone
    _post("replies", {
        "lead_id":         lead_id,
        "gmail_thread_id": thread_id,
        "snippet":         snippet,
        "is_positive":     is_positive,
    })
    updates = {"status": "positive" if is_positive else "replied",
               "replied_at": datetime.now(timezone.utc).isoformat()}
    if snippet:
        updates["reply_snippet"] = snippet
    _patch("leads", "id", lead_id, updates)


def increment_send_count(account_email: str):
    today = date.today().isoformat()
    # Try insert first, then increment on conflict
    h = {**_headers(), "Prefer": "resolution=merge-duplicates,return=minimal"}
    r = requests.post(f"{SUPABASE_URL}/rest/v1/send_counts", headers=h, json={
        "account": account_email, "send_date": today, "count": 1
    })
    if r.status_code in (200, 201):
        return
    # Fallback: fetch current count and update
    existing = _get("send_counts", f"account=eq.{account_email}&send_date=eq.{today}")
    if existing:
        _patch("send_counts", "id", existing[0]["id"], {"count": existing[0]["count"] + 1})


def get_send_log() -> dict:
    """Returns { account: { date: count } } for accounts module."""
    rows = _get("send_counts", "select=account,send_date,count")
    log  = {}
    for r in rows:
        acct = r["account"]
        if acct not in log:
            log[acct] = {}
        log[acct][r["send_date"]] = r["count"]
    return log


def get_lead_by_email(email: str) -> dict | None:
    rows = _get("leads", f"email=eq.{email}&select=id,status")
    return rows[0] if rows else None


if __name__ == "__main__":
    log = get_send_log()
    print("Send log:", json.dumps(log, indent=2))
