"""
Stock discovery collector — finds stocks BEFORE they pump.

Sources:
  1. StockTwits trending — tickers getting unusual social attention
  2. Unusual volume scan via Finnhub — price moving on high volume with no news
  3. Small cap momentum — Google News scanning for breakout mentions
  4. CEO conference calendar — scheduled events that move stocks
  5. Upcoming earnings — companies reporting in next 7 days (Finnhub)

Discovered tickers are stored in a 'discovery' table and surfaced
in the daily brief as "Stocks to Watch" alongside the watchlist.
"""

import os
import re
import sys
import time
from datetime import datetime, timezone, timedelta
from pathlib import Path

import requests
import feedparser

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert, get_db, is_postgres

_HEADERS = {"User-Agent": "StockPulse/1.0"}


# ---------------------------------------------------------------------------
# Ensure discovery table exists
# ---------------------------------------------------------------------------

def _ensure_table():
    ph = "SERIAL" if is_postgres() else "INTEGER"
    auto = "DEFAULT NOW()" if is_postgres() else "DEFAULT CURRENT_TIMESTAMP"
    sql = f"""
        CREATE TABLE IF NOT EXISTS discoveries (
            id          {ph} PRIMARY KEY,
            ticker      TEXT NOT NULL,
            name        TEXT,
            reason      TEXT,
            signal_type TEXT,
            score       REAL DEFAULT 0,
            source      TEXT,
            discovered_at TIMESTAMP {auto}
        )
    """
    try:
        with get_db() as conn:
            conn.cursor().execute(sql)
    except Exception:
        pass


def _save_discovery(ticker: str, name: str, reason: str, signal_type: str, score: float, source: str):
    _ensure_table()
    # Don't duplicate — skip if already discovered in last 24h
    _24h = (datetime.now(timezone.utc) - timedelta(hours=24)).isoformat()
    existing = execute(
        "SELECT id FROM discoveries WHERE ticker = ? AND discovered_at >= ?" if not is_postgres()
        else "SELECT id FROM discoveries WHERE ticker = %s AND discovered_at >= %s",
        (ticker, _24h)
    )
    if existing:
        return
    try:
        insert("discoveries", {
            "ticker":      ticker,
            "name":        name[:100] if name else ticker,
            "reason":      reason[:300],
            "signal_type": signal_type,
            "score":       score,
            "source":      source,
            "discovered_at": datetime.now(timezone.utc).isoformat(),
        })
        print(f"  [Discovery] 🔍 {ticker} — {signal_type}: {reason[:80]}")
    except Exception as e:
        print(f"  [Discovery] DB error {ticker}: {e}")


# ---------------------------------------------------------------------------
# 1. StockTwits trending tickers
# ---------------------------------------------------------------------------

def _collect_trending_stocktwits() -> int:
    found = 0
    try:
        resp = requests.get(
            "https://api.stocktwits.com/api/2/trending/symbols.json",
            headers=_HEADERS, timeout=8
        )
        if resp.status_code != 200:
            return 0

        symbols = resp.json().get("symbols", [])
        # Get our existing watchlist to filter out already-tracked tickers
        watchlist = {r["symbol"] for r in execute(
            "SELECT symbol FROM tickers WHERE watchlist_status IN ('active','watching')"
        )}

        for s in symbols[:20]:
            ticker   = s.get("symbol", "").upper()
            name     = s.get("title", ticker)
            watchlist_count = s.get("watchlist_count", 0)

            if not ticker or ticker in watchlist:
                continue

            # Only flag if meaningful watchlist size (filters out noise)
            if watchlist_count < 500:
                continue

            reason = f"Trending on StockTwits — {watchlist_count:,} users watching"
            _save_discovery(ticker, name, reason, "social_trending", 7.0, "StockTwits Trending")
            found += 1

    except Exception as e:
        print(f"  [Discovery] StockTwits trending: {e}")
    return found


# ---------------------------------------------------------------------------
# 2. Unusual volume / price moves via Finnhub
# ---------------------------------------------------------------------------

def _collect_unusual_movers() -> int:
    api_key = os.getenv("FINNHUB_API_KEY", "")
    if not api_key:
        return 0

    found = 0
    watchlist = {r["symbol"] for r in execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active','watching')"
    )}

    # Finnhub market news — look for tickers not in our watchlist getting coverage
    try:
        resp = requests.get(
            "https://finnhub.io/api/v1/news",
            params={"category": "general", "token": api_key},
            timeout=8
        )
        if resp.status_code != 200:
            return 0

        articles = resp.json()[:30]
        ticker_pattern = re.compile(r'\b([A-Z]{2,5})\b')

        for article in articles:
            headline = article.get("headline", "")
            summary  = article.get("summary", "")
            text     = f"{headline} {summary}"

            # Find tickers mentioned
            for match in ticker_pattern.findall(text):
                if match in watchlist or len(match) < 2:
                    continue
                if match in ("THE", "AND", "FOR", "NEW", "CEO", "IPO", "AI", "US", "UK"):
                    continue

                reason = f"In market news: {headline[:120]}"
                _save_discovery(match, match, reason, "news_mention", 6.0, "Finnhub News")
                found += 1
                if found >= 10:
                    break
            if found >= 10:
                break

    except Exception as e:
        print(f"  [Discovery] Finnhub movers: {e}")
    return found


# ---------------------------------------------------------------------------
# 3. Small cap momentum via Google News
# ---------------------------------------------------------------------------

DISCOVERY_FEEDS = [
    {
        "name":   "Small Cap Breakout",
        "url":    "https://news.google.com/rss/search?q=small+cap+stock+breakout+momentum+surge&hl=en-US&gl=US&ceid=US:en",
        "type":   "small_cap_momentum",
        "score":  7.5,
    },
    {
        "name":   "IPO & New Listings",
        "url":    "https://news.google.com/rss/search?q=IPO+stock+listing+debut+nasdaq+nyse+2025+2026&hl=en-US&gl=US&ceid=US:en",
        "type":   "ipo_watch",
        "score":  7.0,
    },
    {
        "name":   "Analyst Upgrades",
        "url":    "https://news.google.com/rss/search?q=stock+analyst+upgrade+buy+rating+price+target+raised&hl=en-US&gl=US&ceid=US:en",
        "type":   "analyst_upgrade",
        "score":  8.0,
    },
    {
        "name":   "Unusual Options Activity",
        "url":    "https://news.google.com/rss/search?q=unusual+options+activity+call+sweep+dark+pool&hl=en-US&gl=US&ceid=US:en",
        "type":   "options_activity",
        "score":  8.5,
    },
    {
        "name":   "Short Squeeze Candidates",
        "url":    "https://news.google.com/rss/search?q=short+squeeze+high+short+interest+stock&hl=en-US&gl=US&ceid=US:en",
        "type":   "short_squeeze",
        "score":  7.5,
    },
]


def _collect_discovery_feeds() -> int:
    watchlist = {r["symbol"] for r in execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active','watching')"
    )}
    ticker_pattern = re.compile(r'\$([A-Z]{2,5})\b|\b([A-Z]{2,5})\b')
    noise = {"THE", "AND", "FOR", "NEW", "CEO", "IPO", "AI", "US", "UK", "ETF",
             "NYSE", "NASDAQ", "SEC", "FDA", "FED", "GDP", "CPI", "EPS", "QoQ"}

    found = 0
    seen_urls = set()

    for feed_cfg in DISCOVERY_FEEDS:
        try:
            feed    = feedparser.parse(feed_cfg["url"])
            entries = feed.get("entries", [])[:8]

            for entry in entries:
                url = entry.get("link", "")
                if url in seen_urls:
                    continue
                seen_urls.add(url)

                title   = entry.get("title", "")
                summary = entry.get("summary", "")
                text    = f"{title} {summary}"

                # Extract $TICKER mentions first (most reliable)
                dollar_tickers = re.findall(r'\$([A-Z]{2,5})\b', text)
                for t in dollar_tickers:
                    if t not in watchlist and t not in noise:
                        _save_discovery(t, t, f"{feed_cfg['name']}: {title[:120]}", feed_cfg["type"], feed_cfg["score"], feed_cfg["name"])
                        found += 1

        except Exception as e:
            print(f"  [Discovery] {feed_cfg['name']}: {e}")
        time.sleep(0.5)

    return found


# ---------------------------------------------------------------------------
# 4. Upcoming earnings calendar (Finnhub)
# ---------------------------------------------------------------------------

def _collect_earnings_calendar() -> int:
    api_key = os.getenv("FINNHUB_API_KEY", "")
    if not api_key:
        return 0

    found = 0
    try:
        today = datetime.now(timezone.utc).date()
        end   = today + timedelta(days=7)
        resp  = requests.get(
            "https://finnhub.io/api/v1/calendar/earnings",
            params={"from": today.isoformat(), "to": end.isoformat(), "token": api_key},
            timeout=8
        )
        if resp.status_code != 200:
            return 0

        earnings = resp.json().get("earningsCalendar", [])
        watchlist = {r["symbol"] for r in execute(
            "SELECT symbol FROM tickers WHERE watchlist_status IN ('active','watching')"
        )}

        for e in earnings[:50]:
            ticker = (e.get("symbol") or "").upper()
            if not ticker or ticker in watchlist:
                continue
            date_str = e.get("date", "")
            eps_est  = e.get("epsEstimate")
            reason   = f"Earnings report due {date_str}"
            if eps_est:
                reason += f" — EPS estimate: ${eps_est:.2f}"
            _save_discovery(ticker, ticker, reason, "earnings_upcoming", 6.5, "Finnhub Earnings Calendar")
            found += 1

    except Exception as e:
        print(f"  [Discovery] Earnings calendar: {e}")

    return found


# ---------------------------------------------------------------------------
# 5. CEO conference / investor day calendar
# ---------------------------------------------------------------------------

CONFERENCE_CALENDAR_FEEDS = [
    "https://news.google.com/rss/search?q=investor+day+CEO+conference+presentation+2026&hl=en-US&gl=US&ceid=US:en",
    "https://news.google.com/rss/search?q=earnings+call+scheduled+quarterly+results+2026&hl=en-US&gl=US&ceid=US:en",
    "https://news.google.com/rss/search?q=WEF+G7+G20+OPEC+meeting+summit+2026&hl=en-US&gl=US&ceid=US:en",
]


def _collect_conference_calendar() -> int:
    found = 0
    seen  = set()
    for url in CONFERENCE_CALENDAR_FEEDS:
        try:
            feed = feedparser.parse(url)
            for entry in feed.get("entries", [])[:6]:
                title = entry.get("title", "")
                link  = entry.get("link", "")
                if link in seen or not title:
                    continue
                seen.add(link)
                # Store as a MACRO signal so it appears in world events
                try:
                    insert("signals", {
                        "ticker":          "MACRO",
                        "source":          "news",
                        "source_detail":   "Conference Calendar",
                        "content":         f"[EVENT] {title[:300]}",
                        "sentiment":       "neutral",
                        "sentiment_score": 0.0,
                        "raw_score":       5.0,
                        "collected_at":    datetime.now(timezone.utc).isoformat(),
                    })
                    found += 1
                except Exception:
                    pass
        except Exception:
            pass
        time.sleep(0.5)
    return found


# ---------------------------------------------------------------------------
# Main entry point
# ---------------------------------------------------------------------------

def collect() -> dict:
    _ensure_table()
    print("[Discovery] Scanning for new stock opportunities...")

    trending  = _collect_trending_stocktwits()
    movers    = _collect_unusual_movers()
    feeds     = _collect_discovery_feeds()
    earnings  = _collect_earnings_calendar()
    events    = _collect_conference_calendar()

    total = trending + movers + feeds + earnings + events
    print(f"[Discovery] Done — {total} signals (trending:{trending} movers:{movers} feeds:{feeds} earnings:{earnings} events:{events})")
    return {"total": total, "trending": trending, "earnings": earnings}


def get_recent_discoveries(hours: int = 24) -> list[dict]:
    """Return recent discoveries for the Telegram summary."""
    _ensure_table()
    cutoff = (datetime.now(timezone.utc) - timedelta(hours=hours)).isoformat()
    rows = execute(
        "SELECT ticker, name, reason, signal_type, score FROM discoveries WHERE discovered_at >= ? ORDER BY score DESC LIMIT 10" if not is_postgres()
        else "SELECT ticker, name, reason, signal_type, score FROM discoveries WHERE discovered_at >= %s ORDER BY score DESC LIMIT 10",
        (cutoff,)
    )
    return [dict(r) for r in rows]
