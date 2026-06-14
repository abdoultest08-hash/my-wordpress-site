"""
supabase_client.py
Lightweight Supabase REST client for the outreach pipeline.
Updates lead status, logs emails sent, and logs replies.

If Supabase is unreachable (e.g. IP allowlist), operations are written to
local_log.json so they can be synced later via: python3 supabase_client.py --sync
"""
import os
import json
import requests
from datetime import date, datetime, timezone
from pathlib import Path

SUPABASE_URL = os.environ.get("SUPABASE_URL", "")
# Use service key server-side (bypasses RLS) — anon key stays in browser CRM only
SUPABASE_KEY = os.environ.get("SUPABASE_SERVICE_KEY") or os.environ.get("SUPABASE_KEY", "")

BASE      = Path(__file__).parent
LOCAL_LOG = BASE / "local_log.json"

_supabase_ok = None  # cached connectivity check


def _headers():
    return {
        "apikey":        SUPABASE_KEY,
        "Authorization": f"Bearer {SUPABASE_KEY}",
        "Content-Type":  "application/json",
        "Prefer":        "return=representation",
    }


def _check_connectivity() -> bool:
    global _supabase_ok
    if _supabase_ok is not None:
        return _supabase_ok
    try:
        r = requests.get(f"{SUPABASE_URL}/rest/v1/leads?limit=1",
                         headers=_headers(), timeout=5)
        _supabase_ok = r.status_code not in (403,)
    except Exception:
        _supabase_ok = False
    if not _supabase_ok:
        print("  ⚠ Supabase unreachable — logging locally to local_log.json")
    return _supabase_ok


def _local_append(op: dict):
    log = []
    if LOCAL_LOG.exists():
        try:
            log = json.loads(LOCAL_LOG.read_text())
        except Exception:
            pass
    log.append({**op, "_queued_at": datetime.now(timezone.utc).isoformat()})
    LOCAL_LOG.write_text(json.dumps(log, indent=2))


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

    if not _check_connectivity():
        import uuid
        fake_id = str(uuid.uuid4())
        _local_append({"op": "upsert_lead", "data": payload, "fake_id": fake_id})
        return fake_id

    h = {**_headers(), "Prefer": "resolution=merge-duplicates,return=representation"}
    r = requests.post(f"{SUPABASE_URL}/rest/v1/leads", headers=h, json=payload)
    if r.status_code == 409:
        # Already exists — fetch the existing lead id by email
        r2 = requests.get(f"{SUPABASE_URL}/rest/v1/leads?email=eq.{payload['email']}&select=id", headers=_headers())
        if r2.ok and r2.json():
            return r2.json()[0]["id"]
    r.raise_for_status()
    result = r.json()
    return result[0]["id"] if result else None


def mark_site_generated(lead_id: str, screenshot_path: str):
    if not _check_connectivity():
        _local_append({"op": "mark_site_generated", "lead_id": lead_id, "screenshot_path": screenshot_path})
        return
    _patch("leads", "id", lead_id, {
        "status":          "site_generated",
        "screenshot_path": screenshot_path,
    })


def mark_email_sent(lead_id: str, from_account: str, subject: str, gmail_id: str,
                    copy_version: str = "v1", pitch_type: str = "new"):
    data = {
        "status":          "email_sent",
        "email_sent_at":   datetime.now(timezone.utc).isoformat(),
        "email_sent_from": from_account,
        "copy_version":    copy_version,
        "pitch_type":      pitch_type,
    }
    log_entry = {
        "lead_id":          lead_id,
        "from_account":     from_account,
        "subject":          subject,
        "gmail_message_id": gmail_id,
        "status":           "sent",
        "copy_version":     copy_version,
        "pitch_type":       pitch_type,
    }
    if not _check_connectivity():
        _local_append({"op": "mark_email_sent", "lead_id": lead_id, "patch": data, "email_log": log_entry})
        return
    _patch("leads", "id", lead_id, data)
    _post("email_log", log_entry)


def log_reply(lead_id: str, thread_id: str, snippet: str, is_positive: bool):
    reply = {
        "lead_id":         lead_id,
        "gmail_thread_id": thread_id,
        "snippet":         snippet,
        "is_positive":     is_positive,
    }
    updates = {"status": "positive" if is_positive else "replied",
               "replied_at": datetime.now(timezone.utc).isoformat()}
    if snippet:
        updates["reply_snippet"] = snippet

    if not _check_connectivity():
        _local_append({"op": "log_reply", "reply": reply, "patch": updates})
        return
    _post("replies", reply)
    _patch("leads", "id", lead_id, updates)


def increment_send_count(account_email: str):
    today = date.today().isoformat()
    if not _check_connectivity():
        _local_append({"op": "increment_send_count", "account": account_email, "date": today})
        return
    h = {**_headers(), "Prefer": "resolution=merge-duplicates,return=minimal"}
    r = requests.post(f"{SUPABASE_URL}/rest/v1/send_counts", headers=h, json={
        "account": account_email, "send_date": today, "count": 1
    })
    if r.status_code in (200, 201):
        return
    existing = _get("send_counts", f"account=eq.{account_email}&send_date=eq.{today}")
    if existing:
        _patch("send_counts", "id", existing[0]["id"], {"count": existing[0]["count"] + 1})


def get_send_log() -> dict:
    """Returns { account: { date: count } } for accounts module."""
    if not _check_connectivity():
        # Build from local log
        log = {}
        if LOCAL_LOG.exists():
            try:
                entries = json.loads(LOCAL_LOG.read_text())
                for e in entries:
                    if e.get("op") == "increment_send_count":
                        acct = e["account"]
                        d    = e["date"]
                        log.setdefault(acct, {})
                        log[acct][d] = log[acct].get(d, 0) + 1
            except Exception:
                pass
        return log
    rows = _get("send_counts", "select=account,send_date,count")
    log  = {}
    for r in rows:
        acct = r["account"]
        if acct not in log:
            log[acct] = {}
        log[acct][r["send_date"]] = r["count"]
    return log


def get_lead_by_email(email: str) -> dict | None:
    if not _check_connectivity():
        return None
    rows = _get("leads", f"email=eq.{email}&select=id,status")
    return rows[0] if rows else None


def sync_local_log():
    """Push queued local operations to Supabase. Run after fixing connectivity."""
    global _supabase_ok
    _supabase_ok = None  # force re-check
    if not _check_connectivity():
        print("❌ Still can't reach Supabase. Fix the IP allowlist first.")
        return

    if not LOCAL_LOG.exists():
        print("✅ No local log to sync.")
        return

    entries = json.loads(LOCAL_LOG.read_text())
    synced  = 0
    errors  = 0
    id_map  = {}  # fake_id → real_id

    for e in entries:
        op = e.get("op")
        try:
            if op == "upsert_lead":
                h = {**_headers(), "Prefer": "resolution=merge-duplicates,return=representation"}
                r = requests.post(f"{SUPABASE_URL}/rest/v1/leads", headers=h, json=e["data"])
                r.raise_for_status()
                real_id = r.json()[0]["id"]
                id_map[e["fake_id"]] = real_id
                synced += 1

            elif op == "mark_site_generated":
                real_id = id_map.get(e["lead_id"], e["lead_id"])
                _patch("leads", "id", real_id, {"status": "site_generated", "screenshot_path": e["screenshot_path"]})
                synced += 1

            elif op == "mark_email_sent":
                real_id = id_map.get(e["lead_id"], e["lead_id"])
                _patch("leads", "id", real_id, e["patch"])
                log = {**e["email_log"], "lead_id": real_id}
                _post("email_log", log)
                synced += 1

            elif op == "log_reply":
                real_id = id_map.get(e["reply"]["lead_id"], e["reply"]["lead_id"])
                _post("replies", {**e["reply"], "lead_id": real_id})
                _patch("leads", "id", real_id, e["patch"])
                synced += 1

            elif op == "increment_send_count":
                h = {**_headers(), "Prefer": "resolution=merge-duplicates,return=minimal"}
                requests.post(f"{SUPABASE_URL}/rest/v1/send_counts", headers=h, json={
                    "account": e["account"], "send_date": e["date"], "count": 1
                })
                synced += 1

        except Exception as ex:
            print(f"  ✗ Failed to sync {op}: {ex}")
            errors += 1

    LOCAL_LOG.unlink()
    print(f"✅ Synced {synced} operations ({errors} errors). local_log.json removed.")


if __name__ == "__main__":
    import sys
    if "--sync" in sys.argv:
        sync_local_log()
    else:
        log = get_send_log()
        print("Send log:", json.dumps(log, indent=2))
