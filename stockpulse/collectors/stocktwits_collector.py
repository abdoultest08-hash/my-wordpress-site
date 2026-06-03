"""
StockTwits collector — social sentiment from the largest stock-focused social network.

No API key needed. Free public API returns the latest 30 messages per ticker.
StockTwits users tag messages as Bullish/Bearish directly, so sentiment is
more accurate than VADER analysis on Reddit text.

Runs every pipeline cycle alongside news and macro collectors.
"""

import sys
import time
from datetime import datetime, timezone
from pathlib import Path

import requests

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

_API = "https://api.stocktwits.com/api/2/streams/symbol/{ticker}.json?limit=30"
_HEADERS = {"User-Agent": "StockPulse/1.0"}


def _map_sentiment(label: str | None) -> tuple[str, float]:
    """Map StockTwits Bullish/Bearish label to our sentiment scale."""
    if label == "Bullish":
        return "bullish", 0.5
    elif label == "Bearish":
        return "bearish", -0.5
    return "neutral", 0.0


def collect() -> int:
    tickers = execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active', 'watching') AND symbol != 'MACRO'"
    )
    if not tickers:
        return 0

    symbols  = [r["symbol"] for r in tickers]
    saved    = 0
    bullish  = 0
    bearish  = 0

    print(f"[StockTwits] Fetching sentiment for {len(symbols)} tickers...")

    for symbol in symbols:
        try:
            resp = requests.get(
                _API.format(ticker=symbol),
                headers=_HEADERS,
                timeout=8,
            )
            if resp.status_code == 429:
                print(f"  [StockTwits] Rate limited — pausing 60s")
                time.sleep(60)
                resp = requests.get(_API.format(ticker=symbol), headers=_HEADERS, timeout=8)

            if resp.status_code != 200:
                continue

            messages = resp.json().get("messages", [])
            if not messages:
                continue

            # Count bullish vs bearish in this batch
            bull = sum(1 for m in messages if m.get("entities", {}).get("sentiment", {}).get("basic") == "Bullish")
            bear = sum(1 for m in messages if m.get("entities", {}).get("sentiment", {}).get("basic") == "Bearish")
            total_tagged = bull + bear

            # Aggregate sentiment for this ticker
            if total_tagged >= 3:
                bull_ratio = bull / total_tagged
                if bull_ratio >= 0.7:
                    agg_sentiment, agg_score = "very_bullish", 0.7
                elif bull_ratio >= 0.55:
                    agg_sentiment, agg_score = "bullish", 0.4
                elif bull_ratio <= 0.3:
                    agg_sentiment, agg_score = "very_bearish", -0.7
                elif bull_ratio <= 0.45:
                    agg_sentiment, agg_score = "bearish", -0.4
                else:
                    agg_sentiment, agg_score = "neutral", 0.0

                summary = f"{symbol} StockTwits: {bull} bullish / {bear} bearish out of {len(messages)} posts ({bull_ratio*100:.0f}% bullish)"

                try:
                    insert("signals", {
                        "ticker":          symbol,
                        "source":          "stocktwits",
                        "source_detail":   "StockTwits",
                        "content":         summary,
                        "sentiment":       agg_sentiment,
                        "sentiment_score": agg_score,
                        "raw_score":       round((agg_score + 1) * 5, 2),
                        "collected_at":    datetime.now(timezone.utc).isoformat(),
                    })
                    saved += 1
                    bullish += bull
                    bearish += bear
                except Exception:
                    pass

            # Also store top individual messages that have sentiment tags
            for msg in messages[:5]:
                body     = msg.get("body", "").strip()
                st_label = msg.get("entities", {}).get("sentiment", {}).get("basic")
                if not body or not st_label:
                    continue
                sentiment_label, sentiment_score = _map_sentiment(st_label)
                try:
                    insert("signals", {
                        "ticker":          symbol,
                        "source":          "stocktwits",
                        "source_detail":   f"StockTwits @{msg.get('user', {}).get('username', 'user')}",
                        "content":         f"${symbol} {body[:300]}",
                        "sentiment":       sentiment_label,
                        "sentiment_score": sentiment_score,
                        "raw_score":       round((sentiment_score + 1) * 5, 2),
                        "collected_at":    datetime.now(timezone.utc).isoformat(),
                    })
                    saved += 1
                except Exception:
                    pass

        except Exception as e:
            print(f"  [StockTwits] {symbol}: {e}")

        time.sleep(0.5)  # polite rate limiting

    print(f"[StockTwits] Done — {saved} signals | {bullish} bullish / {bearish} bearish tags")
    return saved
