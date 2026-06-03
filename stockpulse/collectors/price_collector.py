"""
Live price collector — Finnhub (primary) with yfinance fallback.

Finnhub: free API key at finnhub.io, set FINNHUB_API_KEY in Railway Variables.
yfinance: no API key, used as fallback if Finnhub fails.
"""

import os
import sys
import time
from datetime import datetime, timezone, timedelta
from pathlib import Path

import requests

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert, upsert_price

try:
    import yfinance as yf
except ImportError:
    yf = None

_FINNHUB_BASE = "https://finnhub.io/api/v1"


def _fetch_finnhub(symbol: str, api_key: str) -> dict | None:
    """Fetch real-time quote from Finnhub."""
    try:
        resp = requests.get(
            f"{_FINNHUB_BASE}/quote",
            params={"symbol": symbol, "token": api_key},
            timeout=8,
        )
        d = resp.json()
        price = float(d.get("c") or 0)   # current price
        prev  = float(d.get("pc") or 0)  # previous close
        if price <= 0:
            return None
        pct_change = round((price - prev) / prev * 100, 2) if prev else 0.0
        return {
            "ticker":     symbol,
            "price":      round(price, 2),
            "prev_close": round(prev, 2),
            "pct_change": pct_change,
            "volume":     int(d.get("v") or 0),
            "fetched_at": datetime.now(timezone.utc).isoformat(),
        }
    except Exception as e:
        print(f"  [Prices] Finnhub {symbol}: {e}")
        return None


def _fetch_yfinance(symbol: str) -> dict | None:
    """Fallback: fetch price via yfinance."""
    if yf is None:
        return None
    try:
        t    = yf.Ticker(symbol)
        info = t.fast_info
        price = float(info.last_price or 0)
        if price <= 0:
            hist = t.history(period="5d")
            if hist.empty:
                return None
            price      = float(hist["Close"].iloc[-1])
            prev_close = float(hist["Close"].iloc[-2]) if len(hist) >= 2 else price
        else:
            prev_close = float(info.previous_close or price)
        pct_change = round((price - prev_close) / prev_close * 100, 2) if prev_close else 0.0
        return {
            "ticker":     symbol,
            "price":      round(price, 2),
            "prev_close": round(prev_close, 2),
            "pct_change": pct_change,
            "volume":     int(getattr(info, "three_month_average_volume", None) or 0),
            "fetched_at": datetime.now(timezone.utc).isoformat(),
        }
    except Exception as e:
        print(f"  [Prices] yfinance {symbol}: {e}")
        return None


def _fetch_one(symbol: str, api_key: str = "") -> dict | None:
    """Try Finnhub first, fall back to yfinance."""
    if api_key:
        data = _fetch_finnhub(symbol, api_key)
        if data:
            return data
    return _fetch_yfinance(symbol)


def collect() -> dict[str, dict]:
    api_key = os.getenv("FINNHUB_API_KEY", "")
    source  = "Finnhub" if api_key else "yfinance"

    tickers = execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active', 'watching') AND symbol != 'MACRO'"
    )
    if not tickers:
        return {}

    symbols = [r["symbol"] for r in tickers]
    print(f"[Prices] Fetching {len(symbols)} prices via {source}...")

    results = {}
    for symbol in symbols:
        data = _fetch_one(symbol, api_key)
        if data:
            results[symbol] = data
            try:
                upsert_price(data)
            except Exception as e:
                print(f"  [Prices] DB error {symbol}: {e}")
        # Finnhub free tier: 60 req/min → ~1 req/sec is safe
        time.sleep(1.1 if api_key else 0.15)

    _detect_price_anomalies(results)
    print(f"[Prices] Done — {len(results)}/{len(symbols)} prices fetched")
    return results


def _detect_price_anomalies(prices: dict[str, dict]):
    for symbol, p in prices.items():
        pct = abs(p.get("pct_change", 0))
        if pct < 3.0:
            continue
        _6h = (datetime.now(timezone.utc) - timedelta(hours=6)).isoformat()
        signals = execute(
            "SELECT COUNT(*) as cnt FROM signals WHERE ticker = ? AND collected_at >= ?",
            (symbol, _6h)
        )
        signal_count = signals[0]["cnt"] if signals else 0
        if signal_count < 3:
            direction = "up" if p["pct_change"] > 0 else "down"
            sentiment = "bullish" if p["pct_change"] > 0 else "bearish"
            content   = (
                f"{symbol} moving {direction} {pct:.1f}% to ${p['price']:.2f} "
                f"with limited news — possible undiscovered catalyst or institutional move."
            )
            print(f"  [Prices] ⚠ Anomaly: {symbol} {p['pct_change']:+.1f}% ({signal_count} signals)")
            try:
                insert("signals", {
                    "ticker":          symbol,
                    "source":          "manual",
                    "source_detail":   "price_anomaly",
                    "content":         content,
                    "sentiment":       sentiment,
                    "sentiment_score": 0.5 if p["pct_change"] > 0 else -0.5,
                    "raw_score":       7.5 if pct > 5 else 6.5,
                    "collected_at":    datetime.now(timezone.utc).isoformat(),
                })
            except Exception:
                pass


def get_price(ticker: str) -> dict | None:
    rows = execute(
        "SELECT * FROM prices WHERE ticker = ? ORDER BY fetched_at DESC LIMIT 1",
        (ticker,)
    )
    return dict(rows[0]) if rows else None


def format_price_line(ticker: str) -> str:
    p = get_price(ticker)
    if not p:
        return ""
    arrow = "▲" if p["pct_change"] >= 0 else "▼"
    return f"${p['price']:.2f} {arrow}{abs(p['pct_change']):.1f}%"
