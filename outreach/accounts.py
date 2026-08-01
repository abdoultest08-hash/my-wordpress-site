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
        "niches":     ["trades"],          # plumbers, roofers, electricians, etc.
    },
    {
        "email":      "pagesforlocals@gmail.com",
        "name":       "Abdoul Sandwidi",
        "token_file": BASE / "token_pagesforlocals.json",
        "start_date": date(2026, 6, 3),
        "niches":     ["trades"],          # second trades account
    },
    # ── Add new accounts below as you create them ─────────────────────────────
    # {
    #     "email":      "sitesforcare@gmail.com",
    #     "name":       "Abdoul Sandwidi",
    #     "token_file": BASE / "token_sitesforcare.json",
    #     "start_date": date(2026, 7, 22),
    #     "niches":     ["care"],
    # },
    # {
    #     "email":      "sitesforaccounts@gmail.com",
    #     "name":       "Abdoul Sandwidi",
    #     "token_file": BASE / "token_sitesforaccounts.json",
    #     "start_date": date(2026, 7, 22),
    #     "niches":     ["accountants"],
    # },
]

# ── Niche → keyword classifier ────────────────────────────────────────────────
NICHE_KEYWORDS = {
    "trades": [
        "plumb", "roof", "gutter", "chimney", "electr", "eletric",
        "clean", "maid", "janitor", "upholstery", "lands", "lawn", "tree",
        "arborist", "sod", "hvac", "heat", "air condition", "mechanical",
        "handy", "remodel", "construct", "contractor", "fence", "concrete",
        "demo", "paint", "pest", "exterminator", "floor", "water damage",
        "restoration", "detail", "car wash",
    ],
    "care": [
        "care", "carer", "caregiver", "home care", "domiciliary", "nursing",
        "elderly", "senior", "supported living", "live-in", "respite",
        "disability", "personal care",
    ],
    "accountants": [
        "accountant", "accounting", "bookkeep", "tax", "payroll", "chartered",
        "cpa", "cfa", "auditor", "financial advisor", "wealth management",
        "finance", "insolvency",
    ],
}

def classify_niche(industry: str) -> str:
    """Return the niche label for an industry string, or 'trades' as default."""
    low = (industry or "").lower()
    for niche, keywords in NICHE_KEYWORDS.items():
        if any(kw in low for kw in keywords):
            return niche
    return "trades"

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
    token_path  = Path(account["token_file"])
    token_data  = json.loads(token_path.read_text())
    creds_data  = json.loads(CREDS_FILE.read_text())["installed"]

    refresh_token = token_data.get("refresh_token") or token_data.get("_refresh_token")

    import requests as req
    if refresh_token:
        r = req.post("https://oauth2.googleapis.com/token", data={
            "client_id":     creds_data["client_id"],
            "client_secret": creds_data["client_secret"],
            "refresh_token": refresh_token,
            "grant_type":    "refresh_token",
        })
        if r.status_code == 200:
            new_token = r.json()["access_token"]
            token_data["access_token"] = new_token
            token_data["token"] = new_token
            token_path.write_text(json.dumps(token_data))

    # Support both "access_token" and "token" key formats
    return token_data.get("access_token") or token_data.get("token")

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

def pick_account(send_log: dict, niche: str = "trades") -> dict | None:
    """Pick the best available account for a given niche today."""
    available = get_available_accounts(send_log)
    if not available:
        return None
    # Prefer accounts whose niche list includes this niche
    niche_matched = [a for a in available if niche in a.get("niches", ["trades"])]
    pool = niche_matched if niche_matched else available
    return max(pool, key=lambda a: a["remaining"])

def pick_draft_account(i: int, niche: str = "trades") -> dict:
    """Round-robin account for draft mode, filtered by niche."""
    niche_accounts = [a for a in ACCOUNTS if niche in a.get("niches", ["trades"])]
    pool = niche_accounts if niche_accounts else ACCOUNTS
    return {**pool[i % len(pool)], "remaining": "∞"}

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
