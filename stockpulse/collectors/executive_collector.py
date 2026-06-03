"""
Executive signal collector — monitors CEO/executive statements and endorsements.

Tracks what major executives are saying publicly. When an executive from
Company A mentions Company B, it creates a high-conviction signal for Company B.

This catches events like:
  - Jensen Huang endorsing Marvell on stage → MRVL signal
  - Elon Musk tweeting about a company → stock moves
  - Tim Cook announcing partnership → partner company signal
  - CEO mentioning competitor threat → bearish signal

No API key needed — uses Google News RSS.
"""

import re
import sys
import time
from datetime import datetime, timezone
from email.utils import parsedate_to_datetime
from pathlib import Path

import feedparser

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer

_analyzer = SentimentIntensityAnalyzer()

# ---------------------------------------------------------------------------
# Executive watchlist — CEO/key executives we track
# ---------------------------------------------------------------------------

EXECUTIVES = [
    # Name,              company, ticker, Google News search query
    ("Jensen Huang",     "NVIDIA",     "NVDA",  "Jensen+Huang+Nvidia"),
    ("Elon Musk",        "Tesla",      "TSLA",  "Elon+Musk+Tesla+SpaceX"),
    ("Tim Cook",         "Apple",      "AAPL",  "Tim+Cook+Apple"),
    ("Satya Nadella",    "Microsoft",  "MSFT",  "Satya+Nadella+Microsoft"),
    ("Sundar Pichai",    "Alphabet",   "GOOGL", "Sundar+Pichai+Google+Alphabet"),
    ("Mark Zuckerberg",  "Meta",       "META",  "Mark+Zuckerberg+Meta+Facebook"),
    ("Alex Karp",        "Palantir",   "PLTR",  "Alex+Karp+Palantir"),
    ("Lisa Su",          "AMD",        "AMD",   "Lisa+Su+AMD"),
    ("Peter Beck",       "Rocket Lab", "RKLB",  "Peter+Beck+Rocket+Lab"),
    ("Andy Jassy",       "Amazon",     "AMZN",  "Andy+Jassy+Amazon+AWS"),
    ("Pat Gelsinger",    "Intel",      "INTC",  "Pat+Gelsinger+Intel"),
    ("Cristiano Amon",   "Qualcomm",   "QCOM",  "Cristiano+Amon+Qualcomm"),
]

# Sentiment boosters for executive statement contexts
_EXEC_WORDS = {
    "endorse": 2.5, "endorses": 2.5, "endorsement": 2.5,
    "partner": 1.8, "partnership": 1.8, "collaborate": 1.5,
    "invest": 1.5, "investment": 1.5, "acquire": 2.0, "acquisition": 2.0,
    "incredible": 2.0, "amazing": 1.8, "best in class": 2.5, "leader": 1.5,
    "recommend": 2.0, "recommends": 2.0,
    "threat": -1.5, "concern": -1.0, "challenging": -1.0, "headwind": -1.5,
    "replace": -1.5, "obsolete": -2.0, "disrupt": -1.0,
    "buy": 1.5, "bought": 1.5, "increased stake": 2.0,
    "sell": -1.5, "sold": -1.5, "divest": -1.5,
}
for word, score in _EXEC_WORDS.items():
    _analyzer.lexicon[word] = score

# Ticker → company name mapping (for cross-ticker detection)
_TICKER_NAMES: dict[str, list[str]] = {}


def _load_ticker_names():
    global _TICKER_NAMES
    rows = execute("SELECT symbol, name FROM tickers WHERE watchlist_status IN ('active', 'watching')")
    for r in rows:
        sym  = r["symbol"]
        name = r["name"].lower()
        # Store multiple name variants for matching
        _TICKER_NAMES[sym] = [
            name,
            name.split()[0],          # first word: "nvidia" from "nvidia corporation"
            sym.lower(),              # ticker itself
        ]


def _find_mentioned_tickers(text: str, exclude_ticker: str) -> list[str]:
    """Find other tickers mentioned in the text (for cross-ticker signals)."""
    if not _TICKER_NAMES:
        _load_ticker_names()

    text_lower = text.lower()
    found = set()

    # Check $TICKER patterns
    for match in re.findall(r'\$([A-Z]{2,5})\b', text.upper()):
        if match in _TICKER_NAMES and match != exclude_ticker:
            found.add(match)

    # Check company name mentions
    for sym, name_variants in _TICKER_NAMES.items():
        if sym == exclude_ticker:
            continue
        for variant in name_variants:
            if len(variant) > 3 and variant in text_lower:
                found.add(sym)
                break

    return list(found)


def _score_sentiment(text: str) -> tuple[str, float]:
    scores   = _analyzer.polarity_scores(text)
    compound = scores["compound"]
    if compound >= 0.5:
        return "very_bullish", round(compound, 3)
    elif compound >= 0.15:
        return "bullish", round(compound, 3)
    elif compound <= -0.5:
        return "very_bearish", round(compound, 3)
    elif compound <= -0.15:
        return "bearish", round(compound, 3)
    return "neutral", round(compound, 3)


def collect() -> int:
    """Collect executive statements and cross-ticker endorsement signals."""
    if not _TICKER_NAMES:
        _load_ticker_names()

    print(f"[Exec] Monitoring {len(EXECUTIVES)} executives...")
    saved = 0
    seen_urls = set()

    for exec_name, company, ticker, query in EXECUTIVES:
        url = f"https://news.google.com/rss/search?q={query}&hl=en-US&gl=US&ceid=US:en"
        try:
            feed    = feedparser.parse(url)
            entries = feed.get("entries", [])[:8]  # top 8 articles per executive

            for entry in entries:
                article_url = entry.get("link", "")
                if article_url in seen_urls:
                    continue
                seen_urls.add(article_url)

                title   = entry.get("title", "")
                summary = entry.get("summary", "")
                text    = f"{title} {summary}".strip()

                # Skip if exec name not in text (feed may include tangential articles)
                exec_first = exec_name.split()[0].lower()
                if exec_first not in text.lower() and exec_name.lower() not in text.lower():
                    continue

                sentiment_label, compound = _score_sentiment(text)

                # Published date
                published_at = None
                for field in ("published", "updated"):
                    val = entry.get(field)
                    if val:
                        try:
                            published_at = parsedate_to_datetime(val).astimezone(timezone.utc).isoformat()
                            break
                        except Exception:
                            pass

                # Primary signal for the executive's own company
                if sentiment_label != "neutral":
                    try:
                        insert("signals", {
                            "ticker":              ticker,
                            "source":              "executive",
                            "source_detail":       f"{exec_name} ({company} CEO)",
                            "content":             f"{exec_name}: {text[:400]}",
                            "url":                 article_url,
                            "sentiment":           sentiment_label,
                            "sentiment_score":     compound,
                            "raw_score":           round((compound + 1) * 5, 2),
                            "collected_at":        datetime.now(timezone.utc).isoformat(),
                            "source_published_at": published_at,
                        })
                        saved += 1
                    except Exception:
                        pass

                # Cross-ticker signals — who else did this exec mention?
                mentioned = _find_mentioned_tickers(text, ticker)
                for other_ticker in mentioned:
                    # Endorsement from major CEO = strong signal for the mentioned company
                    cross_sentiment = sentiment_label
                    cross_compound  = compound
                    # Boost score for endorsement context
                    endorse_words = ["endorses", "endorse", "incredible", "partner", "invest", "buy", "recommend"]
                    if any(w in text.lower() for w in endorse_words):
                        cross_compound = min(1.0, compound + 0.3)
                        cross_sentiment = "very_bullish" if cross_compound >= 0.5 else "bullish"

                    try:
                        insert("signals", {
                            "ticker":              other_ticker,
                            "source":              "executive",
                            "source_detail":       f"Mentioned by {exec_name} ({company})",
                            "content":             f"{exec_name} ({company}) mentioned {other_ticker}: {text[:400]}",
                            "url":                 article_url,
                            "sentiment":           cross_sentiment,
                            "sentiment_score":     cross_compound,
                            "raw_score":           round((cross_compound + 1) * 5, 2) + 1.5,  # bonus for CEO mention
                            "collected_at":        datetime.now(timezone.utc).isoformat(),
                            "source_published_at": published_at,
                        })
                        saved += 1
                        print(f"  [Exec] Cross-signal: {exec_name} mentioned {other_ticker} → {cross_sentiment}")
                    except Exception:
                        pass

        except Exception as e:
            print(f"  [Exec] Error fetching {exec_name}: {e}")

        time.sleep(1.0)

    print(f"[Exec] Done — {saved} executive signals collected")
    return saved


# ---------------------------------------------------------------------------
# Conference / event calendar collector
# ---------------------------------------------------------------------------

CONFERENCE_FEEDS = [
    {
        "name": "Earnings Whispers",
        "url":  "https://news.google.com/rss/search?q=earnings+call+guidance+beat+miss+quarterly&hl=en-US&gl=US&ceid=US:en",
    },
    {
        "name": "Investor Day / Conference",
        "url":  "https://news.google.com/rss/search?q=investor+day+annual+meeting+conference+presentation+CEO&hl=en-US&gl=US&ceid=US:en",
    },
    {
        "name": "M&A / Deals",
        "url":  "https://news.google.com/rss/search?q=acquisition+merger+deal+buyout+stake+investment+billion&hl=en-US&gl=US&ceid=US:en",
    },
    {
        "name": "SEC Filings News",
        "url":  "https://news.google.com/rss/search?q=SEC+filing+13F+form4+insider+buying+selling&hl=en-US&gl=US&ceid=US:en",
    },
]


def collect_events() -> int:
    """Collect conference, earnings, and M&A event signals."""
    if not _TICKER_NAMES:
        _load_ticker_names()

    saved    = 0
    seen_urls = set()

    for feed_cfg in CONFERENCE_FEEDS:
        try:
            feed    = feedparser.parse(feed_cfg["url"])
            entries = feed.get("entries", [])[:10]

            for entry in entries:
                url = entry.get("link", "")
                if url in seen_urls:
                    continue
                seen_urls.add(url)

                title   = entry.get("title", "")
                summary = entry.get("summary", "")
                text    = f"{title} {summary}".strip()

                tickers = _find_mentioned_tickers(text, "")
                if not tickers:
                    continue

                sentiment_label, compound = _score_sentiment(text)
                published_at = None
                for field in ("published", "updated"):
                    val = entry.get(field)
                    if val:
                        try:
                            published_at = parsedate_to_datetime(val).astimezone(timezone.utc).isoformat()
                            break
                        except Exception:
                            pass

                for t in tickers[:4]:
                    try:
                        insert("signals", {
                            "ticker":              t,
                            "source":              "news",
                            "source_detail":       feed_cfg["name"],
                            "content":             text[:500],
                            "url":                 url,
                            "sentiment":           sentiment_label,
                            "sentiment_score":     compound,
                            "raw_score":           round((compound + 1) * 5, 2),
                            "collected_at":        datetime.now(timezone.utc).isoformat(),
                            "source_published_at": published_at,
                        })
                        saved += 1
                    except Exception:
                        pass

        except Exception as e:
            print(f"  [Events] Error: {e}")
        time.sleep(1.0)

    return saved
