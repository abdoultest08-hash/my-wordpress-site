"""
SEC EDGAR insider filing collector.

Monitors Form 4 filings (insider buy/sell) and 8-K filings (material events)
for all tickers in your watchlist. No API key needed — uses SEC EDGAR public API.

Why this matters:
  - A CEO buying $5M of their own stock is one of the strongest bullish signals
  - Cluster buying (multiple insiders buying at once) is even stronger
  - Large insider sells after a run-up = warning sign
  - 8-K filings can reveal contracts, partnerships, or bad news before media picks it up
"""

import sys
import time
from datetime import datetime, timedelta, timezone
from pathlib import Path

import httpx

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

EDGAR_BASE    = "https://data.sec.gov/submissions"
EDGAR_SEARCH  = "https://efts.sec.gov/LATEST/search-index?q=%22{ticker}%22&dateRange=custom&startdt={start}&enddt={end}&forms={form}"
HEADERS       = {"User-Agent": "StockPulse/1.0 abdoultest08@gmail.com"}
REQUEST_DELAY = 1.0

# CIK mapping for our watchlist — pre-mapped to avoid EDGAR lookup latency
# CIK = Central Index Key (SEC's identifier for each company)
TICKER_CIK = {
    "NVDA": "0001045810",
    "AMD":  "0000002488",
    "MSFT": "0000789019",
    "PLTR": "0001321655",
    "ARM":  "0001973935",
    "SMCI": "0000866374",
    "MU":   "0000723254",
    "IONQ": "0001821806",
    "RKLB": "0001819989",
    "ASTS": "0001780059",
    "IRDM": "0001418819",
    "LMT":  "0000936468",
    "KTOS": "0001069258",
    "BWXT": "0000095029",
    "VRT":  "0001643953",
    "EQIX": "0001101239",
    "DLR":  "0001297996",
    "OKLO": "0001849821",
    "CEG":  "0001809090",
    "ENPH": "0001463101",
    "FSLR": "0001274494",
    "PLUG": "0000887936",
    "TSLA": "0001318605",
    "ENVX": "0001745445",
    "QS":   "0001750153",
    "BE":   "0001368308",
    "MRNA": "0001682852",
    "CRSP": "0001674930",
    "ILMN": "0001110803",
    "RXRX": "0001673139",
    "FDX":  "0001048911",
    "XPO":  "0001610922",
    "SAIA": "0001060349",
}

# Transaction codes we care about (Form 4)
BUY_CODES  = {"P", "A"}   # P = open market purchase, A = grant/award
SELL_CODES = {"S", "D"}   # S = open market sale, D = disposition


def _fetch_recent_form4(ticker: str, cik: str, days_back: int = 7) -> list[dict]:
    """Fetch recent Form 4 filings for a ticker from EDGAR."""
    filings = []
    start = (datetime.now() - timedelta(days=days_back)).strftime("%Y-%m-%d")
    end   = datetime.now().strftime("%Y-%m-%d")

    url = f"https://efts.sec.gov/LATEST/search-index?q=%22{ticker}%22&forms=4&dateRange=custom&startdt={start}&enddt={end}"

    try:
        with httpx.Client(headers=HEADERS, timeout=15) as client:
            resp = client.get(url)
            if resp.status_code != 200:
                return []
            data = resp.json()
            hits = data.get("hits", {}).get("hits", [])

            for hit in hits[:10]:
                src = hit.get("_source", {})
                filings.append({
                    "ticker":       ticker,
                    "form_type":    src.get("form_type", "4"),
                    "filed_at":     src.get("file_date", ""),
                    "entity_name":  src.get("entity_name", ""),
                    "description":  src.get("period_of_report", ""),
                    "url":          f"https://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK={cik}&type=4&dateb=&owner=include&count=10",
                })
    except Exception as e:
        print(f"  [SEC] Error fetching Form 4 for {ticker}: {e}")

    return filings


def _fetch_recent_8k(ticker: str, cik: str, days_back: int = 7) -> list[dict]:
    """Fetch recent 8-K filings (material events) for a ticker."""
    filings = []
    start = (datetime.now() - timedelta(days=days_back)).strftime("%Y-%m-%d")
    end   = datetime.now().strftime("%Y-%m-%d")

    url = f"https://efts.sec.gov/LATEST/search-index?q=%22{ticker}%22&forms=8-K&dateRange=custom&startdt={start}&enddt={end}"

    try:
        with httpx.Client(headers=HEADERS, timeout=15) as client:
            resp = client.get(url)
            if resp.status_code != 200:
                return []
            data = resp.json()
            hits = data.get("hits", {}).get("hits", [])

            for hit in hits[:5]:
                src = hit.get("_source", {})
                filings.append({
                    "ticker":      ticker,
                    "form_type":   "8-K",
                    "filed_at":    src.get("file_date", ""),
                    "entity_name": src.get("entity_name", ""),
                    "description": src.get("display_names", [""])[0] if src.get("display_names") else "",
                    "url":         f"https://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK={cik}&type=8-K&dateb=&owner=include&count=10",
                })
    except Exception as e:
        print(f"  [SEC] Error fetching 8-K for {ticker}: {e}")

    return filings


def _fetch_insider_rss(ticker: str) -> list[dict]:
    """
    Use OpenInsider RSS (free, no auth) to get recent Form 4 insider trades
    with actual dollar amounts and transaction details.
    """
    trades = []
    url = f"http://openinsider.com/screener?s={ticker}&o=&pl=&ph=&ll=&lh=&fd=7&fdr=&td=0&tdr=&fdlyl=&fdlyh=&daysago=&xs=1&vl=&vh=&ocl=&och=&sic1=-1&sicl=100&sich=9999&grp=0&nfl=&nfh=&nil=&nih=&nol=&noh=&v2l=&v2h=&oc2l=&oc2h=&sortcol=0&cnt=10&page=1"

    try:
        import feedparser
        # OpenInsider doesn't have RSS, use their CSV export instead
        csv_url = f"http://openinsider.com/screener?s={ticker}&o=&pl=&ph=&ll=&lh=&fd=7&fdr=&td=0&tdr=&fdlyl=&fdlyh=&daysago=&xs=1&vl=100000&vh=&ocl=&och=&sic1=-1&sicl=100&sich=9999&grp=0&nfl=&nfh=&nil=&nih=&nol=&noh=&v2l=&v2h=&oc2l=&oc2h=&sortcol=0&cnt=20&page=1&action=1"

        with httpx.Client(headers={**HEADERS, "Accept": "text/csv"}, timeout=15) as client:
            resp = client.get(csv_url)
            if resp.status_code != 200:
                return []

            lines = resp.text.strip().split("\n")
            if len(lines) < 2:
                return []

            # CSV columns: X,Filing Date,Trade Date,Ticker,Company,Insider Name,Title,Trade Type,Price,Qty,Owned,ΔOwn,Value
            for line in lines[1:]:
                try:
                    parts = line.split(",")
                    if len(parts) < 13:
                        continue

                    trade_type = parts[7].strip().strip('"')
                    value_str  = parts[12].strip().strip('"').replace("$", "").replace(",", "").replace("+", "")
                    price_str  = parts[8].strip().strip('"').replace("$", "").replace(",", "")
                    qty_str    = parts[9].strip().strip('"').replace(",", "").replace("+", "")
                    insider    = parts[5].strip().strip('"')
                    title      = parts[6].strip().strip('"')
                    filed_date = parts[1].strip().strip('"')

                    try:
                        value = abs(float(value_str)) if value_str else 0
                        price = float(price_str) if price_str else 0
                        qty   = int(float(qty_str)) if qty_str else 0
                    except ValueError:
                        continue

                    if value < 50000:  # ignore tiny trades < $50k
                        continue

                    is_buy  = trade_type in ("P - Purchase", "A - Grant")
                    is_sell = trade_type in ("S - Sale", "D - Sale+OE")

                    if not is_buy and not is_sell:
                        continue

                    sentiment = "bullish" if is_buy else "bearish"
                    direction = "BOUGHT" if is_buy else "SOLD"
                    content   = (
                        f"INSIDER {direction}: {insider} ({title}) at {ticker} "
                        f"— ${value:,.0f} worth at ${price:.2f}/share ({qty:,} shares). "
                        f"Filed {filed_date}."
                    )

                    # Score based on size and direction
                    if is_buy:
                        if value >= 1_000_000:
                            raw_score = 9.0
                        elif value >= 500_000:
                            raw_score = 8.0
                        elif value >= 100_000:
                            raw_score = 7.0
                        else:
                            raw_score = 6.0
                        sentiment_score = min(0.9, 0.3 + value / 2_000_000)
                    else:
                        raw_score       = 4.0 if value < 500_000 else 3.0
                        sentiment_score = -0.3

                    trades.append({
                        "ticker":          ticker,
                        "content":         content,
                        "sentiment":       sentiment,
                        "sentiment_score": sentiment_score,
                        "raw_score":       raw_score,
                        "value":           value,
                        "is_buy":          is_buy,
                        "insider":         insider,
                        "title":           title,
                        "filed_at":        filed_date,
                    })

                except Exception:
                    continue

    except Exception as e:
        print(f"  [SEC] OpenInsider error for {ticker}: {e}")

    return trades


def collect() -> int:
    """
    Collect insider trades and 8-K filings for all watchlist tickers.
    Returns number of signals saved.
    """
    tickers = execute(
        "SELECT symbol FROM tickers WHERE watchlist_status IN ('active', 'watching') AND symbol != 'MACRO'"
    )
    if not tickers:
        return 0

    symbols = [r["symbol"] for r in tickers]
    print(f"[SEC] Scanning insider filings for {len(symbols)} tickers...")

    saved = 0
    cluster_buys: dict[str, list] = {}  # ticker → list of buy trades today

    for symbol in symbols:
        trades = _fetch_insider_rss(symbol)

        for trade in trades:
            content = trade["content"]

            # Deduplicate — don't save the same filing twice
            existing = execute(
                "SELECT id FROM signals WHERE ticker = ? AND content = ? AND source = 'sec_filing'",
                (symbol, content[:500])
            )
            if existing:
                continue

            try:
                insert("signals", {
                    "ticker":          symbol,
                    "source":          "sec_filing",
                    "source_detail":   "OpenInsider / SEC Form 4",
                    "content":         content[:1000],
                    "url":             f"http://openinsider.com/{symbol}",
                    "sentiment":       trade["sentiment"],
                    "sentiment_score": trade["sentiment_score"],
                    "raw_score":       trade["raw_score"],
                    "collected_at":    datetime.now(timezone.utc).isoformat(),
                })
                saved += 1

                if trade["is_buy"]:
                    cluster_buys.setdefault(symbol, []).append(trade)

                print(f"  [SEC] {content[:100]}")

            except Exception as e:
                print(f"  [SEC] DB error for {symbol}: {e}")

        time.sleep(REQUEST_DELAY)

    # Detect cluster buying — multiple insiders buying same stock = very strong signal
    for symbol, buys in cluster_buys.items():
        if len(buys) >= 2:
            total_value = sum(b["value"] for b in buys)
            names       = ", ".join(b["insider"] for b in buys[:3])
            content     = (
                f"CLUSTER INSIDER BUYING at {symbol}: {len(buys)} insiders bought "
                f"${total_value:,.0f} total in the last 7 days ({names}). "
                f"Cluster buying is one of the strongest bullish signals."
            )
            print(f"  [SEC] ⚡ Cluster buy detected: {symbol} — {len(buys)} insiders, ${total_value:,.0f}")
            try:
                insert("signals", {
                    "ticker":          symbol,
                    "source":          "sec_filing",
                    "source_detail":   "Cluster Insider Buy",
                    "content":         content,
                    "sentiment":       "very_bullish",
                    "sentiment_score": 0.9,
                    "raw_score":       9.5,
                    "collected_at":    datetime.now(timezone.utc).isoformat(),
                })
                saved += 1
            except Exception:
                pass

    print(f"[SEC] Done — {saved} insider signals saved")
    return saved
