"""
accounts.py
Manages multiple Gmail sending accounts with warm-up schedules
and human-like randomised sending windows (9am-5pm Mon-Fri).
"""
import json
import random
from datetime import datetime, date, timedelta
from pathlib import Path
from zoneinfo import ZoneInfo

BASE = Path(__file__).parent

# ── Account registry ──────────────────────────────────────────────────────────
# Add new accounts here as you create them.
# token_file: path to the token JSON for this account
# start_date: the date the account was first used (for warm-up calculation)
ACCOUNTS = [
    {
        "email":      "sitesbyabs@gmail.com",
        "name":       "Abdoul Sandwidi",
        "token_file": BASE / "token.json",
        "start_date": date(2026, 6, 3),
    },
    {
        "email":      "pagesforlocals@gmail.com",
        "name":       "Abdoul Sandwidi",
        "token_file": BASE / "token_pagesforlocals.json",
        "start_date": date(2026, 6, 3),
    },
]

# ── Warm-up schedule ──────────────────────────────────────────────────────────
# Returns max emails allowed per day based on account age
def daily_limit(start_date: date) -> int:
    age_days = (date.today() - start_date).days
    if age_days < 5:   return 10
    if age_days < 10:  return 15
    if age_days < 20:  return 20
    if age_days < 40:  return 30
    return 40  # max per account per day

# ── Send time scheduler ───────────────────────────────────────────────────────
TIMEZONE  = ZoneInfo("America/Los_Angeles")
DAY_START = 9   # 9am
DAY_END   = 17  # 5pm

def is_business_hours() -> bool:
    now = datetime.now(TIMEZONE)
    return (
        now.weekday() < 5 and               # Mon-Fri
        DAY_START <= now.hour < DAY_END
    )

def seconds_until_next_window() -> int:
    """Seconds until 9am next business day."""
    now  = datetime.now(TIMEZONE)
    next = now.replace(hour=DAY_START, minute=0, second=0, microsecond=0)
    if now.hour >= DAY_END or now.weekday() >= 5:
        next += timedelta(days=1)
        while next.weekday() >= 5:
            next += timedelta(days=1)
    return max(0, int((next - now).total_seconds()))

def random_send_delay(emails_remaining: int) -> int:
    """
    Returns seconds to wait before sending the next email.
    Spreads emails_remaining across the rest of the business day.
    """
    now           = datetime.now(TIMEZONE)
    end_of_day    = now.replace(hour=DAY_END, minute=0, second=0, microsecond=0)
    secs_left     = max(0, int((end_of_day - now).total_seconds()))
    if emails_remaining <= 1 or secs_left <= 0:
        return random.randint(60, 180)
    base_gap      = secs_left // emails_remaining
    # Add ±30% jitter so sends don't look robotic
    jitter        = int(base_gap * 0.3)
    return max(60, base_gap + random.randint(-jitter, jitter))

# ── Token helpers ─────────────────────────────────────────────────────────────
CREDS_FILE = BASE / "credentials.json"

def get_access_token(account: dict) -> str:
    """Return a valid access token for an account, refreshing if needed."""
    token_data  = json.loads(Path(account["token_file"]).read_text())
    creds_data  = json.loads(CREDS_FILE.read_text())["installed"]

    import requests as req
    if "refresh_token" in token_data:
        r = req.post("https://oauth2.googleapis.com/token", data={
            "client_id":     creds_data["client_id"],
            "client_secret": creds_data["client_secret"],
            "refresh_token": token_data["refresh_token"],
            "grant_type":    "refresh_token",
        })
        if r.status_code == 200:
            token_data["access_token"] = r.json()["access_token"]
            Path(account["token_file"]).write_text(json.dumps(token_data))

    return token_data["access_token"]

# ── Account picker ────────────────────────────────────────────────────────────
def get_available_accounts(send_log: dict) -> list[dict]:
    """
    Returns accounts that haven't hit their daily limit yet today.
    send_log: { "email": { "YYYY-MM-DD": count } }
    """
    today     = date.today().isoformat()
    available = []
    for acct in ACCOUNTS:
        sent_today = send_log.get(acct["email"], {}).get(today, 0)
        limit      = daily_limit(acct["start_date"])
        remaining  = limit - sent_today
        if remaining > 0:
            available.append({**acct, "remaining": remaining, "sent_today": sent_today})
    return available

def pick_account(send_log: dict) -> dict | None:
    """Pick the account with the most remaining quota today (round-robin style)."""
    available = get_available_accounts(send_log)
    if not available:
        return None
    # Pick the one with most remaining to balance load
    return max(available, key=lambda a: a["remaining"])

def log_send(send_log: dict, email: str) -> dict:
    """Increment the send count for an account."""
    today = date.today().isoformat()
    if email not in send_log:
        send_log[email] = {}
    send_log[email][today] = send_log[email].get(today, 0) + 1
    return send_log


if __name__ == "__main__":
    print("Account status:")
    for acct in ACCOUNTS:
        limit = daily_limit(acct["start_date"])
        age   = (date.today() - acct["start_date"]).days
        print(f"  {acct['email']} — day {age}, limit {limit}/day")
    print(f"\nBusiness hours now: {is_business_hours()}")
    print(f"Secs until next window: {seconds_until_next_window()}")
