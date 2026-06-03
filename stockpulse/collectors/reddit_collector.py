"""
Reddit collector — disabled (Railway IP blocked by Reddit 403).
StockTwits collector handles social sentiment instead.
"""

import json
import re
import time
from datetime import datetime, timezone
from pathlib import Path

import httpx
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer

import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

SUBREDDITS = [
    "stocks",
    "investing",
    "wallstreetbets",
    "SecurityAnalysis",
    "Futurology",
    "space",
]

SORT_MODES = ["hot", "new"]         # fetch both hot and new posts
POSTS_PER_FETCH = 25                # Reddit public feed max per request
REQUEST_DELAY = 2.0                 # seconds between requests (be polite)

HEADERS = {
    "User-Agent": "StockPulse/1.0 (stock signal scanner; contact: stockpulse@gmail.com)"
}

# ---------------------------------------------------------------------------
# Ticker extraction
# ---------------------------------------------------------------------------

# Load known tickers from the database
def _load_watchlist() -> dict[str, str]:
    """Return {symbol: name} for all active/watching tickers."""
    rows = execute(
        "SELECT symbol, name FROM tickers WHERE watchlist_status IN ('active', 'watching')"
    )
    return {r["symbol"]: r["name"] for r in rows}


def _extract_tickers(text: str, watchlist: dict[str, str]) -> list[str]:
    """
    Find ticker symbols in text.
    Matches $NVDA style and plain uppercase words like NVDA that are in the watchlist.
    """
    found = set()

    # $TICKER pattern — explicit callout
    dollar_tickers = re.findall(r'\$([A-Z]{2,5})\b', text.upper())
    for t in dollar_tickers:
        if t in watchlist:
            found.add(t)

    # Plain uppercase — e.g. "bought more NVDA today"
    upper_words = re.findall(r'\b([A-Z]{2,5})\b', text)
    for t in upper_words:
        if t in watchlist:
            found.add(t)

    return list(found)


# ---------------------------------------------------------------------------
# Sentiment scoring
# ---------------------------------------------------------------------------

_analyzer = SentimentIntensityAnalyzer()

# Finance-specific boosts for words VADER doesn't weight heavily enough
_FINANCE_WORDS = {
    "moon": 2.0, "mooning": 2.0, "rocket": 1.5, "calls": 0.5,
    "puts": -0.5, "short": -0.8, "squeeze": 1.5, "bullish": 2.0,
    "bearish": -2.0, "bagholding": -1.5, "bagholder": -1.5,
    "undervalued": 1.5, "overvalued": -1.5, "bankruptcy": -3.0,
    "fraud": -3.0, "rally": 1.5, "dump": -1.5, "crash": -2.5,
    "pump": 1.0, "breakout": 1.5, "catalyst": 1.0, "earnings beat": 2.0,
    "earnings miss": -2.0, "revenue growth": 1.5, "layoffs": -1.0,
}

for word, score in _FINANCE_WORDS.items():
    _analyzer.lexicon[word] = score


def _score_sentiment(text: str) -> tuple[str, float]:
    """
    Returns (label, compound_score).
    compound_score: -1.0 (most bearish) to +1.0 (most bullish)
    label: very_bullish / bullish / neutral / bearish / very_bearish
    """
    scores = _analyzer.polarity_scores(text)
    compound = scores["compound"]

    if compound >= 0.5:
        label = "very_bullish"
    elif compound >= 0.15:
        label = "bullish"
    elif compound <= -0.5:
        label = "very_bearish"
    elif compound <= -0.15:
        label = "bearish"
    else:
        label = "neutral"

    return label, round(compound, 3)


def _compound_to_raw_score(compound: float) -> float:
    """Convert -1..+1 compound score to 0..10 raw score."""
    return round((compound + 1) * 5, 2)


# ---------------------------------------------------------------------------
# Reddit fetcher
# ---------------------------------------------------------------------------

def _fetch_posts(subreddit: str, sort: str, client: httpx.Client) -> list[dict]:
    """Fetch posts from one subreddit/sort combination."""
    url = f"https://www.reddit.com/r/{subreddit}/{sort}.json?limit={POSTS_PER_FETCH}"
    try:
        resp = client.get(url, headers=HEADERS, timeout=10)
        if resp.status_code == 200:
            data = resp.json()
            return data.get("data", {}).get("children", [])
        elif resp.status_code == 429:
            print(f"  [Reddit] Rate limited on r/{subreddit} — waiting 30s")
            time.sleep(30)
        else:
            print(f"  [Reddit] HTTP {resp.status_code} for r/{subreddit}/{sort}")
    except Exception as e:
        print(f"  [Reddit] Error fetching r/{subreddit}/{sort}: {e}")
    return []


# ---------------------------------------------------------------------------
# Main collector
# ---------------------------------------------------------------------------

def collect() -> int:
    """Disabled — Reddit blocks Railway IPs with 403. Returns 0."""
    print("[Reddit] Skipped — blocked by Reddit (use StockTwits instead)")
    return 0


def _collect_disabled() -> int:
    """Original implementation kept for reference but not called."""
    print(f"[Reddit] Starting collection — {len(SUBREDDITS)} subreddits")
    watchlist = _load_watchlist()
    if not watchlist:
        print("[Reddit] No tickers in watchlist — skipping")
        return 0

    saved = 0
    seen_post_ids = set()   # avoid processing the same post twice

    with httpx.Client() as client:
        for subreddit in SUBREDDITS:
            for sort in SORT_MODES:
                print(f"  [Reddit] Fetching r/{subreddit}/{sort}...")
                posts = _fetch_posts(subreddit, sort, client)

                for post in posts:
                    p = post.get("data", {})
                    post_id = p.get("id", "")

                    if post_id in seen_post_ids:
                        continue
                    seen_post_ids.add(post_id)

                    title = p.get("title", "")
                    body  = p.get("selftext", "")
                    full_text = f"{title} {body}".strip()

                    tickers = _extract_tickers(full_text, watchlist)
                    if not tickers:
                        continue

                    sentiment_label, compound = _score_sentiment(full_text)
                    raw_score = _compound_to_raw_score(compound)

                    # Published timestamp from Reddit (Unix epoch)
                    created_utc = p.get("created_utc")
                    published_at = (
                        datetime.fromtimestamp(created_utc, tz=timezone.utc).isoformat()
                        if created_utc else None
                    )

                    url = f"https://reddit.com{p.get('permalink', '')}"

                    for ticker in tickers:
                        try:
                            insert("signals", {
                                "ticker":               ticker,
                                "source":               "reddit",
                                "source_detail":        f"r/{subreddit}",
                                "content":              full_text[:1000],   # cap length
                                "url":                  url,
                                "sentiment":            sentiment_label,
                                "sentiment_score":      compound,
                                "raw_score":            raw_score,
                                "collected_at":         datetime.now(timezone.utc).isoformat(),
                                "source_published_at":  published_at,
                            })
                            saved += 1
                        except Exception as e:
                            print(f"    [Reddit] DB error for {ticker}: {e}")

                time.sleep(REQUEST_DELAY)

    print(f"[Reddit] Done — {saved} signals saved")
    return saved


# ---------------------------------------------------------------------------
# Run standalone for testing
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    count = collect()
    print(f"\nTotal signals collected: {count}")

    # Show a summary of what was found
    from database.db import execute as db_exec
    rows = db_exec("""
        SELECT ticker, COUNT(*) as mentions,
               ROUND(AVG(sentiment_score), 3) as avg_sentiment
        FROM signals
        WHERE source = 'reddit'
        GROUP BY ticker
        ORDER BY mentions DESC
    """)
    if rows:
        print("\nTicker summary:")
        print(f"  {'Ticker':<8} {'Mentions':<10} {'Avg Sentiment'}")
        print(f"  {'-'*30}")
        for r in rows:
            bar = "▲" if r["avg_sentiment"] > 0.1 else ("▼" if r["avg_sentiment"] < -0.1 else "—")
            print(f"  {r['ticker']:<8} {r['mentions']:<10} {r['avg_sentiment']:>6}  {bar}")
