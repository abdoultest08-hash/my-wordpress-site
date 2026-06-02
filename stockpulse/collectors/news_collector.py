"""
News collector — scans financial news RSS feeds for ticker mentions.

Sources used:
  - Yahoo Finance RSS (free, no API key)
  - NewsAPI.org (optional, set NEWS_API_KEY in .env for broader coverage)
  - Google News RSS (free, no API key)
"""

import os
import re
import time
from datetime import datetime, timezone
from email.utils import parsedate_to_datetime
from pathlib import Path

import feedparser
import httpx
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer

import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

# ---------------------------------------------------------------------------
# RSS feed sources (no API key needed)
# ---------------------------------------------------------------------------

RSS_FEEDS = [
    # ── Yahoo Finance ──────────────────────────────────────────────────────
    {"name": "Yahoo Finance: Markets",      "url": "https://finance.yahoo.com/rss/topfinstories",                                                                    "theme": "general"},
    {"name": "Yahoo Finance: Tech",         "url": "https://finance.yahoo.com/rss/industry?ind=semiconductors",                                                      "theme": "Tech / AI"},
    # ── MarketWatch ────────────────────────────────────────────────────────
    {"name": "MarketWatch: Top Stories",    "url": "https://feeds.marketwatch.com/marketwatch/topstories/",                                                          "theme": "general"},
    {"name": "MarketWatch: Tech",           "url": "https://feeds.marketwatch.com/marketwatch/marketpulse/",                                                         "theme": "Tech / AI"},
    # ── Reuters ────────────────────────────────────────────────────────────
    {"name": "Reuters: Business",           "url": "https://feeds.reuters.com/reuters/businessNews",                                                                 "theme": "general"},
    {"name": "Reuters: Tech",               "url": "https://feeds.reuters.com/reuters/technologyNews",                                                               "theme": "Tech / AI"},
    # ── CNBC ───────────────────────────────────────────────────────────────
    {"name": "CNBC: Top News",              "url": "https://search.cnbc.com/rs/search/combinedcms/view.xml?partnerId=wrss01&id=100003114",                           "theme": "general"},
    {"name": "CNBC: Tech",                  "url": "https://search.cnbc.com/rs/search/combinedcms/view.xml?partnerId=wrss01&id=19854910",                            "theme": "Tech / AI"},
    {"name": "CNBC: Investing",             "url": "https://search.cnbc.com/rs/search/combinedcms/view.xml?partnerId=wrss01&id=15839069",                            "theme": "general"},
    # ── Investing.com ──────────────────────────────────────────────────────
    {"name": "Investing.com: Stock News",   "url": "https://www.investing.com/rss/news_25.rss",                                                                     "theme": "general"},
    {"name": "Investing.com: Tech",         "url": "https://www.investing.com/rss/news_14.rss",                                                                     "theme": "Tech / AI"},
    # ── Seeking Alpha ──────────────────────────────────────────────────────
    {"name": "Seeking Alpha: Markets",      "url": "https://seekingalpha.com/market_currents.xml",                                                                  "theme": "general"},
    # ── Google News per sector ─────────────────────────────────────────────
    {"name": "Google News: AI Stocks",      "url": "https://news.google.com/rss/search?q=AI+semiconductor+nvidia+amd+stocks&hl=en-US&gl=US&ceid=US:en",             "theme": "Tech / AI"},
    {"name": "Google News: Space Stocks",   "url": "https://news.google.com/rss/search?q=space+rocket+satellite+stock+SpaceX+RocketLab&hl=en-US&gl=US&ceid=US:en", "theme": "Space / Defense"},
    {"name": "Google News: Clean Energy",   "url": "https://news.google.com/rss/search?q=solar+energy+EV+battery+clean+tech+stock&hl=en-US&gl=US&ceid=US:en",      "theme": "Energy"},
    {"name": "Google News: Big Tech",       "url": "https://news.google.com/rss/search?q=Apple+Meta+Google+Microsoft+earnings+stock&hl=en-US&gl=US&ceid=US:en",    "theme": "Big Tech"},
    {"name": "Google News: Biotech",        "url": "https://news.google.com/rss/search?q=biotech+FDA+clinical+trial+stock&hl=en-US&gl=US&ceid=US:en",              "theme": "Biotech"},
    {"name": "Google News: Macro",          "url": "https://news.google.com/rss/search?q=Federal+Reserve+interest+rates+economy+tariffs&hl=en-US&gl=US&ceid=US:en","theme": "Macro"},
    # ── Per-ticker Yahoo Finance news ──────────────────────────────────────
    {"name": "Yahoo Finance: NVDA",         "url": "https://finance.yahoo.com/rss/headline?s=NVDA",  "theme": "Tech / AI",    "ticker_hint": "NVDA"},
    {"name": "Yahoo Finance: TSLA",         "url": "https://finance.yahoo.com/rss/headline?s=TSLA",  "theme": "EV / Energy",  "ticker_hint": "TSLA"},
    {"name": "Yahoo Finance: PLTR",         "url": "https://finance.yahoo.com/rss/headline?s=PLTR",  "theme": "Tech / AI",    "ticker_hint": "PLTR"},
    {"name": "Yahoo Finance: AAPL",         "url": "https://finance.yahoo.com/rss/headline?s=AAPL",  "theme": "Big Tech",     "ticker_hint": "AAPL"},
    {"name": "Yahoo Finance: META",         "url": "https://finance.yahoo.com/rss/headline?s=META",  "theme": "Big Tech",     "ticker_hint": "META"},
    {"name": "Yahoo Finance: GOOGL",        "url": "https://finance.yahoo.com/rss/headline?s=GOOGL", "theme": "Big Tech",     "ticker_hint": "GOOGL"},
    {"name": "Yahoo Finance: AMD",          "url": "https://finance.yahoo.com/rss/headline?s=AMD",   "theme": "Tech / AI",    "ticker_hint": "AMD"},
    {"name": "Yahoo Finance: MSFT",         "url": "https://finance.yahoo.com/rss/headline?s=MSFT",  "theme": "Big Tech",     "ticker_hint": "MSFT"},
    {"name": "Yahoo Finance: RKLB",         "url": "https://finance.yahoo.com/rss/headline?s=RKLB",  "theme": "Space",        "ticker_hint": "RKLB"},
    {"name": "Yahoo Finance: ASML",         "url": "https://finance.yahoo.com/rss/headline?s=ASML",  "theme": "Tech / AI",    "ticker_hint": "ASML"},
]

REQUEST_DELAY = 1.5     # seconds between feed fetches

# ---------------------------------------------------------------------------
# Sentiment analyser (same finance-tuned VADER as Reddit collector)
# ---------------------------------------------------------------------------

_analyzer = SentimentIntensityAnalyzer()

_FINANCE_WORDS = {
    "beat": 1.5, "beats": 1.5, "miss": -1.5, "misses": -1.5,
    "upgrade": 2.0, "downgrade": -2.0, "outperform": 1.5, "underperform": -1.5,
    "bullish": 2.0, "bearish": -2.0, "rally": 1.5, "crash": -2.5,
    "surge": 2.0, "soar": 2.0, "plunge": -2.0, "tumble": -1.5, "slide": -1.0,
    "record high": 2.5, "all-time high": 2.5, "52-week high": 2.0,
    "record low": -2.5, "52-week low": -2.0, "bankruptcy": -3.0,
    "acquisition": 1.5, "merger": 1.0, "ipo": 1.5, "spinoff": 0.8,
    "layoffs": -1.0, "restructuring": -0.5, "guidance raised": 2.0,
    "guidance lowered": -2.0, "revenue growth": 1.5, "profit warning": -2.5,
    "short seller": -1.5, "fraud": -3.0, "sec investigation": -2.5,
    "contract win": 2.0, "partnership": 1.0, "breakthrough": 2.0,
}

for word, score in _FINANCE_WORDS.items():
    _analyzer.lexicon[word] = score


def _score_sentiment(text: str) -> tuple[str, float]:
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
    return round((compound + 1) * 5, 2)


# ---------------------------------------------------------------------------
# Ticker extraction
# ---------------------------------------------------------------------------

def _load_watchlist() -> dict[str, str]:
    rows = execute(
        "SELECT symbol, name FROM tickers WHERE watchlist_status IN ('active', 'watching')"
    )
    return {r["symbol"]: r["name"] for r in rows}


def _extract_tickers(text: str, watchlist: dict[str, str]) -> list[str]:
    found = set()

    # $TICKER style
    for t in re.findall(r'\$([A-Z]{2,5})\b', text.upper()):
        if t in watchlist:
            found.add(t)

    # Plain uppercase words
    for t in re.findall(r'\b([A-Z]{2,5})\b', text):
        if t in watchlist:
            found.add(t)

    # Company name mentions — e.g. "Nvidia" → NVDA
    text_lower = text.lower()
    name_to_symbol = {name.lower(): sym for sym, name in watchlist.items()}
    for name_lower, sym in name_to_symbol.items():
        first_word = name_lower.split()[0]
        if len(first_word) > 3 and first_word in text_lower:
            found.add(sym)

    return list(found)


# ---------------------------------------------------------------------------
# Published date parser
# ---------------------------------------------------------------------------

def _parse_date(entry) -> str | None:
    for field in ("published", "updated"):
        val = entry.get(field)
        if val:
            try:
                return parsedate_to_datetime(val).astimezone(timezone.utc).isoformat()
            except Exception:
                pass
    return None


# ---------------------------------------------------------------------------
# NewsAPI (optional — only runs if NEWS_API_KEY is set)
# ---------------------------------------------------------------------------

def _collect_newsapi(watchlist: dict[str, str], api_key: str) -> int:
    """Pull top financial headlines from NewsAPI and extract signals."""
    saved = 0
    queries = [
        "stock market semiconductor AI",
        "SpaceX rocket satellite defense stock",
        "clean energy EV solar battery stock",
        "earnings revenue guidance stocks",
    ]
    base_url = "https://newsapi.org/v2/everything"

    with httpx.Client() as client:
        for q in queries:
            try:
                resp = client.get(base_url, params={
                    "q": q, "language": "en", "sortBy": "publishedAt",
                    "pageSize": 20, "apiKey": api_key,
                }, timeout=10)
                if resp.status_code != 200:
                    continue
                articles = resp.json().get("articles", [])
                for article in articles:
                    text = f"{article.get('title','')} {article.get('description','')}".strip()
                    tickers = _extract_tickers(text, watchlist)
                    if not tickers:
                        continue
                    sentiment_label, compound = _score_sentiment(text)
                    raw_score = _compound_to_raw_score(compound)
                    published = article.get("publishedAt")
                    source_name = article.get("source", {}).get("name", "NewsAPI")
                    for ticker in tickers:
                        try:
                            insert("signals", {
                                "ticker":               ticker,
                                "source":               "news",
                                "source_detail":        source_name,
                                "content":              text[:1000],
                                "url":                  article.get("url"),
                                "sentiment":            sentiment_label,
                                "sentiment_score":      compound,
                                "raw_score":            raw_score,
                                "collected_at":         datetime.now(timezone.utc).isoformat(),
                                "source_published_at":  published,
                            })
                            saved += 1
                        except Exception as e:
                            print(f"    [NewsAPI] DB error for {ticker}: {e}")
                time.sleep(REQUEST_DELAY)
            except Exception as e:
                print(f"  [NewsAPI] Error for query '{q}': {e}")
    return saved


# ---------------------------------------------------------------------------
# Main collector
# ---------------------------------------------------------------------------

def collect() -> int:
    """Fetch all RSS feeds and optional NewsAPI, save signals. Returns count saved."""
    print(f"[News] Starting collection — {len(RSS_FEEDS)} RSS feeds")
    watchlist = _load_watchlist()
    if not watchlist:
        print("[News] No tickers in watchlist — skipping")
        return 0

    saved = 0
    seen_urls = set()

    for feed_cfg in RSS_FEEDS:
        print(f"  [News] Fetching: {feed_cfg['name']}")
        try:
            feed = feedparser.parse(feed_cfg["url"])
            entries = feed.get("entries", [])
            print(f"    → {len(entries)} articles")

            ticker_hint = feed_cfg.get("ticker_hint")  # per-ticker feeds already know the symbol

            for entry in entries:
                url = entry.get("link", "")
                if url in seen_urls:
                    continue
                seen_urls.add(url)

                title   = entry.get("title", "")
                summary = entry.get("summary", "")
                full_text = f"{title} {summary}".strip()

                if ticker_hint:
                    tickers = [ticker_hint]
                else:
                    tickers = _extract_tickers(full_text, watchlist)
                if not tickers:
                    continue

                sentiment_label, compound = _score_sentiment(full_text)
                raw_score = _compound_to_raw_score(compound)
                published_at = _parse_date(entry)

                for ticker in tickers:
                    try:
                        insert("signals", {
                            "ticker":               ticker,
                            "source":               "news",
                            "source_detail":        feed_cfg["name"],
                            "content":              full_text[:1000],
                            "url":                  url,
                            "sentiment":            sentiment_label,
                            "sentiment_score":      compound,
                            "raw_score":            raw_score,
                            "collected_at":         datetime.now(timezone.utc).isoformat(),
                            "source_published_at":  published_at,
                        })
                        saved += 1
                    except Exception as e:
                        print(f"    [News] DB error for {ticker}: {e}")

        except Exception as e:
            print(f"  [News] Error fetching {feed_cfg['name']}: {e}")

        time.sleep(REQUEST_DELAY)

    # Optional NewsAPI top-up
    api_key = os.getenv("NEWS_API_KEY", "")
    if api_key:
        print("  [News] NewsAPI key found — fetching additional articles...")
        saved += _collect_newsapi(watchlist, api_key)
    else:
        print("  [News] No NEWS_API_KEY set — using RSS only (add key to .env for more coverage)")

    print(f"[News] Done — {saved} signals saved")
    return saved


# ---------------------------------------------------------------------------
# Run standalone for testing
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    from dotenv import load_dotenv
    load_dotenv(Path(__file__).parent.parent / ".env")

    count = collect()
    print(f"\nTotal signals collected: {count}")

    from database.db import execute as db_exec
    rows = db_exec("""
        SELECT ticker, COUNT(*) as mentions,
               ROUND(AVG(sentiment_score), 3) as avg_sentiment
        FROM signals
        WHERE source = 'news'
        GROUP BY ticker
        ORDER BY mentions DESC
    """)
    if rows:
        print("\nTicker summary:")
        print(f"  {'Ticker':<8} {'Mentions':<10} {'Avg Sentiment'}")
        print(f"  {'-'*32}")
        for r in rows:
            bar = "▲" if r["avg_sentiment"] > 0.1 else ("▼" if r["avg_sentiment"] < -0.1 else "—")
            print(f"  {r['ticker']:<8} {r['mentions']:<10} {r['avg_sentiment']:>6}  {bar}")
