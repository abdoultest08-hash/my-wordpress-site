"""
StockPulse configuration loader.

Reads environment variables (from .env in development, from Railway env vars
in production) and the investor_profile.json, then exposes everything as a
single typed Config object that every other module imports.
"""

import json
import os
import sys
from dataclasses import dataclass, field
from pathlib import Path
from typing import Optional

from dotenv import load_dotenv
from supabase import Client, create_client

# Load .env file when running locally (Railway sets env vars directly)
_env_path = Path(__file__).parent / ".env"
load_dotenv(_env_path)

_profile_path = Path(__file__).parent / "investor_profile.json"


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _require(var: str) -> str:
    """Return env var value or exit with a clear error message."""
    value = os.getenv(var)
    if not value:
        print(f"[StockPulse] ERROR: required environment variable '{var}' is not set.", file=sys.stderr)
        print(f"  → Add it to stockpulse/.env (local) or Railway environment variables (production).", file=sys.stderr)
        sys.exit(1)
    return value


def _optional(var: str, default: str = "") -> str:
    return os.getenv(var, default)


# ---------------------------------------------------------------------------
# Dataclasses — typed config sections
# ---------------------------------------------------------------------------

@dataclass
class SupabaseConfig:
    url: str
    key: str
    client: Client = field(init=False, repr=False)

    def __post_init__(self):
        self.client = create_client(self.url, self.key)


@dataclass
class RedditConfig:
    client_id: str
    client_secret: str
    user_agent: str
    communities: list[str]
    enabled: bool = True


@dataclass
class NewsConfig:
    api_key: str
    feeds: list[str]
    enabled: bool = True


@dataclass
class TwitterConfig:
    bearer_token: str
    enabled: bool = True


@dataclass
class EmailConfig:
    sendgrid_api_key: str
    from_address: str
    to_address: str
    digest_time: str        # "07:00"
    digest_timezone: str    # "America/New_York"


@dataclass
class SmsConfig:
    account_sid: str
    auth_token: str
    from_number: str
    to_number: str
    enabled: bool = True


@dataclass
class ScoringConfig:
    conviction_alert_threshold: float
    weights: dict[str, float]
    sector_boosts: dict[str, float]     # theme name → multiplier
    market_cap_weights: dict[str, float]
    max_risk_tier: str
    scan_interval_minutes: int


@dataclass
class Config:
    supabase: SupabaseConfig
    reddit: RedditConfig
    news: NewsConfig
    twitter: TwitterConfig
    email: EmailConfig
    sms: SmsConfig
    scoring: ScoringConfig
    openai_api_key: Optional[str]
    investor_profile: dict      # raw profile for reference


# ---------------------------------------------------------------------------
# Builder — reads env + profile, returns Config
# ---------------------------------------------------------------------------

def load_config() -> Config:
    """Load and validate all configuration. Call once at startup."""

    if not _profile_path.exists():
        print(f"[StockPulse] ERROR: investor_profile.json not found at {_profile_path}", file=sys.stderr)
        sys.exit(1)

    with open(_profile_path) as f:
        profile = json.load(f)

    # --- Supabase ---
    supabase = SupabaseConfig(
        url=_require("SUPABASE_URL"),
        key=_require("SUPABASE_KEY"),
    )

    # --- Reddit ---
    communities = [
        c for src in profile["signal_sources"]["reddit"]["communities"]
        for c in [src]  # already a list of subreddit names in the profile
    ]
    reddit = RedditConfig(
        client_id=_require("REDDIT_CLIENT_ID"),
        client_secret=_require("REDDIT_CLIENT_SECRET"),
        user_agent=_optional("REDDIT_USER_AGENT", "StockPulse/1.0"),
        communities=communities,
        enabled=profile["signal_sources"]["reddit"]["enabled"],
    )

    # --- News ---
    news = NewsConfig(
        api_key=_require("NEWS_API_KEY"),
        feeds=profile["signal_sources"]["news"]["feeds"],
        enabled=profile["signal_sources"]["news"]["enabled"],
    )

    # --- Twitter ---
    twitter_enabled = profile["signal_sources"]["ceo_social"]["enabled"]
    twitter = TwitterConfig(
        bearer_token=_optional("TWITTER_BEARER_TOKEN"),
        enabled=twitter_enabled,
    )

    # --- Email ---
    digest_cfg = profile["notifications"]["daily_digest"]
    email = EmailConfig(
        sendgrid_api_key=_require("SENDGRID_API_KEY"),
        from_address=_optional("EMAIL_FROM", "stockpulse@yourdomain.com"),
        to_address=_optional("EMAIL_TO", profile["investor"]["email"]),
        digest_time=digest_cfg["time"],
        digest_timezone=digest_cfg.get("timezone", "America/New_York"),
    )

    # --- SMS ---
    sms_enabled = "sms" in profile["notifications"]["instant_alert"]["channels"]
    sms = SmsConfig(
        account_sid=_optional("TWILIO_ACCOUNT_SID"),
        auth_token=_optional("TWILIO_AUTH_TOKEN"),
        from_number=_optional("TWILIO_FROM_NUMBER"),
        to_number=_optional("SMS_TO_NUMBER"),
        enabled=sms_enabled,
    )

    # --- Scoring weights and sector boosts from profile ---
    raw_weights = profile["scoring"]["score_weights"]
    sector_boosts = {s["name"]: s["weight"] for s in profile["sectors"]}
    cap_weights = {c["tier"]: c["weight"] for c in profile["market_cap_focus"]}

    scoring = ScoringConfig(
        conviction_alert_threshold=float(profile["scoring"]["conviction_alert_threshold"]),
        weights=raw_weights,
        sector_boosts=sector_boosts,
        market_cap_weights=cap_weights,
        max_risk_tier=profile["risk_tolerance"]["max_risk_tier"],
        scan_interval_minutes=profile["deployment"]["scan_interval_minutes"],
    )

    return Config(
        supabase=supabase,
        reddit=reddit,
        news=news,
        twitter=twitter,
        email=email,
        sms=sms,
        scoring=scoring,
        openai_api_key=_optional("OPENAI_API_KEY") or None,
        investor_profile=profile,
    )


# ---------------------------------------------------------------------------
# Module-level singleton — import config from anywhere with:
#   from stockpulse.config import config
# ---------------------------------------------------------------------------
config: Config = load_config()
