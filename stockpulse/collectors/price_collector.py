"""
Live price collector — fetches real-time stock prices via yfinance (Yahoo Finance).

No API key required. Runs every pipeline cycle to:
  - Store current price, % change, volume in the prices table
  - Detect unusual price moves (price spiking with no news = signal)
  - Enrich SMS alerts with current price context
"""

import sys
from datetime import datetime, timezone, timedelta
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert, upsert_price

try:
    import yfinance as yf
except ImportError:
    yf = None


def collect() -> dict[str, dict]:
    """
    Fetch live prices for all active tickers.
    Returns dict of {ticker: price_data}.
    """
    if yf is None:
        print("[Prices] yfinance not installed — skipping")
        return {}

    tickers = execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active', 'watching') AND symbol != 'MACRO'"
    )
    if not tickers:
        return {}

    symbols = [r["symbol"] for r in tickers]
    print(f"[Prices] Fetching live prices for {len(symbols)} tickers...")

    results = {}
    try:
        # Use 5d period so weekends/holidays still return the last trading day's close
        data = yf.download(
            tickers=" ".join(symbols),
            period="5d",
            interval="1d",
            group_by="ticker",
            auto_adjust=True,
            progress=False,
            threads=True,
        )

        for symbol in symbols:
            try:
                if len(symbols) == 1:
                    ticker_data = data
                else:
                    ticker_data = data[symbol]

                if ticker_data is None or ticker_data.empty:
                    continue

                latest = ticker_data.iloc[-1]
                prev   = ticker_data.iloc[-2] if len(ticker_data) >= 2 else latest

                price      = float(latest["Close"])
                prev_close = float(prev["Close"])
                pct_change = round((price - prev_close) / prev_close * 100, 2) if prev_close else 0.0
                volume     = int(latest["Volume"]) if "Volume" in latest else 0

                price_data = {
                    "ticker":     symbol,
                    "price":      round(price, 2),
                    "prev_close": round(prev_close, 2),
                    "pct_change": pct_change,
                    "volume":     volume,
                    "fetched_at": datetime.now(timezone.utc).isoformat(),
                }
                results[symbol] = price_data

                try:
                    upsert_price(price_data)
                except Exception as e:
                    print(f"  [Prices] DB error for {symbol}: {e}")

            except Exception as e:
                print(f"  [Prices] Error parsing {symbol}: {e}")

    except Exception as e:
        print(f"[Prices] Batch fetch error: {e}")
        # Fall back to individual fetches
        for symbol in symbols:
            try:
                t = yf.Ticker(symbol)
                info = t.fast_info
                price      = float(info.last_price or 0)
                prev_close = float(info.previous_close or price)
                pct_change = round((price - prev_close) / prev_close * 100, 2) if prev_close else 0.0
                volume     = int(info.three_month_average_volume or 0)

                price_data = {
                    "ticker":     symbol,
                    "price":      round(price, 2),
                    "prev_close": round(prev_close, 2),
                    "pct_change": pct_change,
                    "volume":     volume,
                    "fetched_at": datetime.now(timezone.utc).isoformat(),
                }
                results[symbol] = price_data
                try:
                    upsert_price(price_data)
                except Exception:
                    pass
            except Exception:
                pass

    # Detect unusual moves — price spike with no news
    _detect_price_anomalies(results)

    print(f"[Prices] Done — {len(results)} prices fetched")
    return results


def _detect_price_anomalies(prices: dict[str, dict]):
    """
    Flag tickers with large price moves (>3%) but low signal count.
    These get stored as manual signals to boost the next scoring cycle.
    """
    for symbol, p in prices.items():
        pct = abs(p.get("pct_change", 0))
        if pct < 3.0:
            continue

        # Check if we have news explaining the move
        _6h = (datetime.now(timezone.utc) - timedelta(hours=6)).isoformat()
        signals = execute(
            "SELECT COUNT(*) as cnt FROM signals WHERE ticker = ? AND collected_at >= ?",
            (symbol, _6h)
        )
        signal_count = signals[0]["cnt"] if signals else 0

        if signal_count < 3:
            direction  = "up" if p["pct_change"] > 0 else "down"
            sentiment  = "bullish" if p["pct_change"] > 0 else "bearish"
            score      = 0.5 if p["pct_change"] > 0 else -0.5
            content    = (
                f"{symbol} is moving {direction} {pct:.1f}% to ${p['price']:.2f} "
                f"with limited news coverage — possible undiscovered catalyst or institutional move."
            )
            print(f"  [Prices] ⚠ Anomaly: {symbol} {p['pct_change']:+.1f}% with only {signal_count} signals")
            try:
                insert("signals", {
                    "ticker":          symbol,
                    "source":          "manual",
                    "source_detail":   "price_anomaly",
                    "content":         content,
                    "sentiment":       sentiment,
                    "sentiment_score": score,
                    "raw_score":       7.5 if pct > 5 else 6.5,
                    "collected_at":    datetime.now(timezone.utc).isoformat(),
                })
            except Exception:
                pass


def get_price(ticker: str) -> dict | None:
    """Get the most recently stored price for a ticker."""
    rows = execute(
        "SELECT * FROM prices WHERE ticker = ? ORDER BY fetched_at DESC LIMIT 1",
        (ticker,)
    )
    return dict(rows[0]) if rows else None


def format_price_line(ticker: str) -> str:
    """Return a formatted price string for SMS use, e.g. '$138.50 (+2.3%)'"""
    p = get_price(ticker)
    if not p:
        return ""
    arrow = "▲" if p["pct_change"] >= 0 else "▼"
    return f"${p['price']:.2f} {arrow}{abs(p['pct_change']):.1f}%"
