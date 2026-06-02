"""
Signal scorer — converts raw signals into a 1–10 conviction score per ticker.

Scoring formula (weights from investor_profile.json):
  reddit_sentiment   20%
  news_sentiment     25%
  ceo_signal         20%
  thematic_cascade   20%
  sector_alignment   15%

Each component produces a 0–10 sub-score.
Final conviction score = weighted average, then adjusted for:
  - signal volume (more signals = more confidence)
  - recency (signals in last 6h count more than 24h-old signals)
  - market cap tier (small-cap needs stronger signal to score high)
"""

import json
import math
from datetime import datetime, timedelta, timezone
from pathlib import Path

import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, upsert_daily_score

# ---------------------------------------------------------------------------
# Load investor profile
# ---------------------------------------------------------------------------

_PROFILE_PATH = Path(__file__).parent.parent / "investor_profile.json"

with open(_PROFILE_PATH) as f:
    _PROFILE = json.load(f)

_WEIGHTS       = _PROFILE["scoring"]["score_weights"]
_SECTOR_BOOSTS = {s["name"]: s["weight"] for s in _PROFILE["sectors"]}
_CAP_WEIGHTS   = {c["tier"]: c["weight"] for c in _PROFILE["market_cap_focus"]}
_ALERT_THRESH  = float(_PROFILE["scoring"]["conviction_alert_threshold"])
_MAX_RISK_TIER = _PROFILE["risk_tolerance"]["max_risk_tier"]

# ---------------------------------------------------------------------------
# Risk tier classifier (used inside scorer, full version in risk_classifier.py)
# ---------------------------------------------------------------------------

_RISK_ORDER = ["Low", "Medium", "High", "Speculative"]

def _classify_risk(ticker_info: dict, conviction: float, avg_sentiment: float) -> str:
    cap = ticker_info.get("market_cap_tier", "mid-cap")

    # Base risk from market cap
    if cap in ("mega-cap",):
        base = "Low"
    elif cap in ("large-cap",):
        base = "Medium"
    elif cap in ("mid-cap",):
        base = "Medium"
    else:
        base = "High"   # small/micro cap

    # Boost risk if sentiment is very negative despite high conviction
    # (contrarian signal — could be squeeze or trap)
    if avg_sentiment < -0.3 and conviction > 6:
        base = _escalate(base)

    # Very high conviction with small-cap = speculative
    if cap in ("small-cap", "micro-cap") and conviction > 7.5:
        base = "Speculative"

    return base


def _escalate(tier: str) -> str:
    idx = _RISK_ORDER.index(tier)
    return _RISK_ORDER[min(idx + 1, len(_RISK_ORDER) - 1)]


# ---------------------------------------------------------------------------
# Recency weight — signals decay over 24h
# ---------------------------------------------------------------------------

def _recency_weight(collected_at_str: str) -> float:
    """
    Returns 1.0 for a signal collected now, decaying to 0.2 at 24h old.
    Signals older than 48h get weight 0.1.
    """
    try:
        ts = datetime.fromisoformat(collected_at_str.replace("Z", "+00:00"))
        age_hours = (datetime.now(timezone.utc) - ts).total_seconds() / 3600
    except Exception:
        return 0.5

    if age_hours <= 6:
        return 1.0
    elif age_hours <= 24:
        return 1.0 - 0.8 * ((age_hours - 6) / 18)   # linear decay 1.0 → 0.2
    else:
        return max(0.1, 0.2 - 0.1 * ((age_hours - 24) / 24))


# ---------------------------------------------------------------------------
# Sub-score calculators (each returns 0–10)
# ---------------------------------------------------------------------------

def _sentiment_subscore(signals: list[dict], source_filter: str | None = None) -> float:
    """
    Weighted average sentiment for signals from a given source (or all sources).
    Returns 0–10 (5.0 = perfectly neutral).
    """
    filtered = [s for s in signals if source_filter is None or s["source"] == source_filter]
    if not filtered:
        return 5.0  # neutral when no data

    total_weight = 0.0
    weighted_sum = 0.0

    for s in filtered:
        w = _recency_weight(s["collected_at"])
        score = float(s["sentiment_score"] or 0)
        weighted_sum += score * w
        total_weight += w

    if total_weight == 0:
        return 5.0

    avg = weighted_sum / total_weight           # -1.0 to +1.0
    return round((avg + 1) * 5, 2)              # scale to 0–10


def _volume_confidence_multiplier(signal_count: int) -> float:
    """
    More signals = more confidence in the score.
    1 signal → 0.7x, 5 signals → 1.0x, 10+ signals → 1.15x
    """
    if signal_count <= 0:
        return 0.5
    return min(1.15, 0.7 + 0.06 * signal_count)


def _sector_alignment_score(theme: str | None) -> float:
    """
    Returns 0–10 based on how well the ticker's theme matches your preferred sectors.
    A perfect match gets 10, unknown/unmatched gets 5.
    """
    if not theme:
        return 5.0
    for sector_name, boost in _SECTOR_BOOSTS.items():
        # Partial match — e.g. "AI / Semiconductors" matches "Tech / AI / Semiconductors"
        if any(word in theme for word in sector_name.split(" / ")):
            return min(10.0, 5.0 * boost * 1.2)
    return 5.0


def _cap_tier_adjustment(cap_tier: str | None) -> float:
    """
    Multiplier based on market cap preference from investor profile.
    Large-cap = 1.0, mid-cap = 1.1, small-cap = 0.9
    """
    return _CAP_WEIGHTS.get(cap_tier or "mid-cap", 1.0)


# ---------------------------------------------------------------------------
# Main scoring function
# ---------------------------------------------------------------------------

def score_ticker(ticker: str) -> dict | None:
    """
    Score a single ticker based on all signals collected today.
    Returns a score dict ready to be written to daily_scores, or None if no data.
    """
    # Load ticker metadata
    ticker_rows = execute(
        "SELECT symbol, name, theme, sector, market_cap_tier FROM tickers WHERE symbol = ?",
        (ticker,)
    )
    if not ticker_rows:
        return None
    ticker_info = ticker_rows[0]

    # Load all signals for this ticker (last 48h)
    cutoff = (datetime.now(timezone.utc) - timedelta(hours=48)).isoformat()
    signals = execute(
        "SELECT * FROM signals WHERE ticker = ? AND collected_at >= ?",
        (ticker, cutoff)
    )
    if not signals:
        return None

    # --- Component sub-scores ---
    reddit_score   = _sentiment_subscore(signals, "reddit")
    news_score     = _sentiment_subscore(signals, "news")
    ceo_score      = _sentiment_subscore(signals, "ceo_social")
    # cascade_score loaded separately from cascade_events table
    cascade_score  = _get_cascade_score(ticker)
    sector_score   = _sector_alignment_score(ticker_info.get("theme"))

    # --- Weighted conviction (0–10) ---
    raw_conviction = (
        reddit_score  * _WEIGHTS["reddit_sentiment"]  +
        news_score    * _WEIGHTS["news_sentiment"]     +
        ceo_score     * _WEIGHTS["ceo_signal"]         +
        cascade_score * _WEIGHTS["thematic_cascade"]   +
        sector_score  * _WEIGHTS["sector_alignment"]
    )

    # Apply volume confidence and market cap adjustments
    vol_mult = _volume_confidence_multiplier(len(signals))
    cap_mult = _cap_tier_adjustment(ticker_info.get("market_cap_tier"))

    conviction = round(min(10.0, raw_conviction * vol_mult * cap_mult), 2)

    # --- Risk tier ---
    avg_sentiment = sum(float(s["sentiment_score"] or 0) for s in signals) / len(signals)
    risk_tier = _classify_risk(ticker_info, conviction, avg_sentiment)

    # --- Yesterday's score for delta ---
    _yesterday = (datetime.now(timezone.utc) - timedelta(days=1)).date().isoformat()
    prev = execute(
        "SELECT conviction_score FROM daily_scores WHERE ticker = ? AND date = ?",
        (ticker, _yesterday)
    )
    prev_score = float(prev[0]["conviction_score"]) if prev else None

    # --- Plain-English reasoning ---
    reasoning = _build_reasoning(
        ticker, conviction, risk_tier, len(signals),
        reddit_score, news_score, cascade_score, sector_score, ticker_info
    )

    return {
        "ticker":           ticker,
        "date":             datetime.now(timezone.utc).date().isoformat(),
        "conviction_score": conviction,
        "risk_tier":        risk_tier,
        "signal_count":     len(signals),
        "reddit_score":     round(reddit_score, 2),
        "news_score":       round(news_score, 2),
        "ceo_signal_score": round(ceo_score, 2),
        "cascade_score":    round(cascade_score, 2),
        "sector_alignment": round(sector_score, 2),
        "reasoning":        reasoning,
        "prev_day_score":   prev_score,
    }


def _get_cascade_score(ticker: str) -> float:
    """
    Check if this ticker appears as an affected ticker in any recent cascade event.
    Returns 0–10 based on proximity score.
    """
    cutoff = (datetime.now(timezone.utc) - timedelta(hours=48)).isoformat()

    # SQLite stores affected_tickers as a JSON text array
    rows = execute(
        "SELECT proximity_scores, affected_tickers FROM cascade_events WHERE detected_at >= ?",
        (cutoff,)
    )
    best_score = 5.0  # neutral default

    for row in rows:
        try:
            affected = json.loads(row["affected_tickers"] or "[]")
            if ticker not in affected:
                continue
            proximity = json.loads(row["proximity_scores"] or "{}")
            p = float(proximity.get(ticker, 0.5))
            # Convert 0–1 proximity to 0–10 score (0.8 proximity → 9.0 score)
            cascade_score = round(5.0 + p * 5.0, 2)
            best_score = max(best_score, cascade_score)
        except Exception:
            continue

    return best_score


def _build_reasoning(ticker, conviction, risk_tier, signal_count,
                     reddit, news, cascade, sector, ticker_info) -> str:
    parts = []

    if conviction >= 8:
        parts.append(f"Strong conviction signal on {ticker}")
    elif conviction >= 6:
        parts.append(f"Moderate conviction on {ticker}")
    else:
        parts.append(f"Weak/mixed signal on {ticker}")

    if news > 7:
        parts.append("news sentiment is strongly bullish")
    elif news < 4:
        parts.append("news sentiment is negative")

    if reddit > 7:
        parts.append("Reddit community is bullish")
    elif reddit < 4:
        parts.append("Reddit sentiment is bearish")

    if cascade > 7:
        parts.append("benefits from a thematic cascade event")

    if sector > 8:
        parts.append(f"strong sector alignment ({ticker_info.get('theme', '')})")

    cap = ticker_info.get("market_cap_tier", "")
    if cap in ("small-cap", "micro-cap"):
        parts.append("small-cap — higher risk")
    elif cap == "mega-cap":
        parts.append("mega-cap — lower risk")

    parts.append(f"{signal_count} signal(s) collected in last 48h")
    return ". ".join(parts) + "."


# ---------------------------------------------------------------------------
# Score all active tickers and persist to daily_scores
# ---------------------------------------------------------------------------

def score_all() -> list[dict]:
    """
    Score every active ticker and write results to daily_scores.
    Returns list of score dicts sorted by conviction descending.
    """
    tickers = execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active', 'watching')"
    )
    results = []

    for row in tickers:
        ticker = row["symbol"]
        result = score_ticker(ticker)
        if result:
            upsert_daily_score(result)
            results.append(result)

    results.sort(key=lambda x: x["conviction_score"], reverse=True)
    return results


# ---------------------------------------------------------------------------
# Run standalone for testing
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    print("Running scorer against mock data...\n")
    results = score_all()

    if not results:
        print("No results — run: python database/mock_data.py first")
    else:
        print(f"{'Ticker':<7} {'Score':>6}  {'Risk':<12}  Reasoning")
        print("-" * 80)
        for r in results:
            delta = ""
            if r["prev_day_score"] is not None:
                d = r["conviction_score"] - r["prev_day_score"]
                delta = f" ({'↑' if d > 0 else '↓'}{abs(d):.1f})"
            print(f"{r['ticker']:<7} {r['conviction_score']:>5.1f}{delta:<8}  [{r['risk_tier']:<12}]  {r['reasoning'][:80]}")

        print(f"\n🔔 Alert threshold: conviction ≥ {_ALERT_THRESH}")
        alerts = [r for r in results if r["conviction_score"] >= _ALERT_THRESH]
        if alerts:
            print(f"   {len(alerts)} ticker(s) would trigger an instant alert:")
            for r in alerts:
                print(f"   ⚡ {r['ticker']} — {r['conviction_score']}/10 [{r['risk_tier']}]")
        else:
            print("   No tickers above threshold today")
