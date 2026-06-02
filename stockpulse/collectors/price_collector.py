"""
Live price collector — Yahoo Finance via yfinance (no API key needed).

Uses individual Ticker fetches instead of batch download for reliability on Railway.
Runs every pipeline cycle to store price/change/volume and detect anomalies.
"""

import sys
import time
from datetime import datetime, timezone, timedelta
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert, upsert_price

try:
    import yfinance as yf
except ImportError:
    yf = None


def _fetch_one(symbol: str) -> dict | None:
    """Fetch price for a single ticker. Returns price dict or None."""
    try:
        t    = yf.Ticker(symbol)
        info = t.fast_info

        price = float(info.last_price or 0)
        if price <= 0:
            # fast_info failed — fall back to history
            hist = t.history(period="5d")
            if hist.empty:
                return None
            price      = float(hist["Close"].iloc[-1])
            prev_close = float(hist["Close"].iloc[-2]) if len(hist) >= 2 else price
        else:
            prev_close = float(info.previous_close or price)

        pct_change = round((price - prev_close) / prev_close * 100, 2) if prev_close else 0.0
        volume     = int(getattr(info, "three_month_average_volume", None) or 0)

        return {
            "ticker":     symbol,
            "price":      round(price, 2),
            "prev_close": round(prev_close, 2),
            "pct_change": pct_change,
            "volume":     volume,
            "fetched_at": datetime.now(timezone.utc).isoformat(),
        }
    except Exception as e:
        print(f"  [Prices] {symbol}: {e}")
        return None


def collect() -> dict[str, dict]:
    if yf is None:
        print("[Prices] yfinance not installed — skipping")
        return {}

    tickers = execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active', 'watching') AND symbol != 'MACRO'"
    )
    if not tickers:
        return {}

    symbols = [r["symbol"] for r in tickers]
    print(f"[Prices] Fetching prices for {len(symbols)} tickers...")

    results = {}
    for symbol in symbols:
        data = _fetch_one(symbol)
        if data:
            results[symbol] = data
            try:
                upsert_price(data)
            except Exception as e:
                print(f"  [Prices] DB error {symbol}: {e}")
        # Small delay to avoid rate limiting
        time.sleep(0.15)

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
