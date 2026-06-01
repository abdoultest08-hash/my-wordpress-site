"""
Injects realistic mock signals into the local database for testing.
Run this to populate data so the scoring engine and alerts can be tested
without needing live Reddit/news feeds.
"""

import random
import sys
from datetime import datetime, timedelta, timezone
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent.parent))
from database.db import execute, insert

random.seed(42)

MOCK_SIGNALS = [
    # NVDA — very bullish (earnings beat + AI demand)
    ("NVDA", "news",   "NVIDIA beats earnings by 25%, data center revenue triples year over year on AI demand", "very_bullish", 0.82),
    ("NVDA", "news",   "Goldman Sachs raises NVDA price target to $1200, cites unstoppable AI infrastructure buildout", "very_bullish", 0.76),
    ("NVDA", "reddit", "NVDA is the picks and shovels play of the AI gold rush. Revenue visibility is insane.", "bullish", 0.61),
    ("NVDA", "reddit", "Bought more NVDA calls this morning. Blackwell shipments ahead of schedule per supply chain checks.", "very_bullish", 0.71),
    ("NVDA", "news",   "Microsoft and Meta both increasing GPU orders from NVIDIA for 2025 AI capex cycle", "bullish", 0.58),

    # RKLB — bullish (launch success + contracts)
    ("RKLB", "news",   "Rocket Lab successfully launches 50th Electron mission, signs new DoD contract worth $140M", "very_bullish", 0.79),
    ("RKLB", "reddit", "RKLB finally getting the recognition it deserves. Neutron development ahead of schedule.", "bullish", 0.52),
    ("RKLB", "news",   "Rocket Lab wins NASA contract for ESCAPADE Mars mission, first interplanetary mission for private launch", "bullish", 0.64),

    # ASTS — mixed (promising but cash burn concerns)
    ("ASTS", "news",   "AST SpaceMobile completes first direct-to-cell satellite broadband test with AT&T, speeds exceed 10Mbps", "very_bullish", 0.81),
    ("ASTS", "reddit", "ASTS cash burn is terrifying. They'll need another capital raise before commercial launch.", "bearish", -0.42),
    ("ASTS", "news",   "AST SpaceMobile partnership with Vodafone expands to 12 European markets", "bullish", 0.55),

    # PLTR — bullish (government contracts)
    ("PLTR", "news",   "Palantir wins $480M US Army AI contract extension, government revenue growth accelerating", "very_bullish", 0.74),
    ("PLTR", "reddit", "PLTR is undervalued given their AIP platform traction. Every defense contractor is adopting it.", "bullish", 0.59),
    ("PLTR", "news",   "Palantir added to S&P 500, index fund buying expected to drive significant volume", "bullish", 0.67),

    # TSLA — mixed (EV competition pressure)
    ("TSLA", "news",   "Tesla Q3 deliveries miss estimates by 8%, pricing pressure from BYD intensifying in China", "bearish", -0.51),
    ("TSLA", "reddit", "Tesla FSD 12.5 is genuinely impressive. Robotaxi launch could re-rate the entire stock.", "bullish", 0.48),
    ("TSLA", "news",   "Elon Musk confirms Tesla Robotaxi launch event scheduled, autonomous driving regulatory approval pending", "bullish", 0.55),

    # ENPH — bearish (interest rate headwinds)
    ("ENPH", "news",   "Enphase Energy cuts revenue guidance citing high interest rates suppressing solar installations", "very_bearish", -0.72),
    ("ENPH", "reddit", "ENPH guidance cut was brutal. Residential solar is dead until rates come down.", "bearish", -0.58),
    ("ENPH", "news",   "Solar installer bankruptcies rising, Enphase inventory buildup worsening into Q4", "bearish", -0.44),

    # AMD — bullish (AI GPU competition)
    ("AMD", "news",   "AMD MI300X GPU adoption accelerating, Microsoft and Meta deploying at scale for AI training", "bullish", 0.63),
    ("AMD", "reddit", "AMD is the only real competition to NVDA and it's priced at a steep discount. No brainer.", "bullish", 0.57),

    # ARM — bullish (AI chip architecture royalties)
    ("ARM", "news",   "Arm Holdings reports 45% royalty revenue growth driven by AI chip designs using v9 architecture", "very_bullish", 0.78),
    ("ARM", "reddit", "ARM royalties are like printing money. Every AI chip from Apple to Qualcomm runs on their ISA.", "bullish", 0.62),

    # SMCI — very bearish (accounting concerns)
    ("SMCI", "news",   "Super Micro Computer delays annual report filing, auditor Ernst & Young resigns over accounting concerns", "very_bearish", -0.91),
    ("SMCI", "reddit", "SMCI is a fraud risk. Auditor resignation is a massive red flag. Stay far away.", "very_bearish", -0.84),

    # IRDM — neutral/slight bullish
    ("IRDM", "news",   "Iridium Communications raises full-year ARPU guidance, IoT subscriber growth ahead of plan", "bullish", 0.44),

    # FSLR — slight bullish
    ("FSLR", "news",   "First Solar wins 3GW utility-scale contract, largest in company history, factory utilisation at 95%", "bullish", 0.66),
]


def seed():
    """Insert mock signals with realistic timestamps spread over the last 24 hours."""
    print("Seeding mock signals...")
    now = datetime.now(timezone.utc)

    existing = execute("SELECT COUNT(*) as c FROM signals WHERE source IN ('reddit','news')")
    if existing and existing[0]["c"] > 0:
        print(f"  {existing[0]['c']} signals already exist — skipping seed")
        return

    for i, (ticker, source, content, sentiment, score) in enumerate(MOCK_SIGNALS):
        # Spread timestamps over last 24h
        hours_ago = random.uniform(0.5, 23)
        collected_at = (now - timedelta(hours=hours_ago)).isoformat()

        insert("signals", {
            "ticker":           ticker,
            "source":           source,
            "source_detail":    "r/wallstreetbets" if source == "reddit" else "Mock News",
            "content":          content,
            "sentiment":        sentiment,
            "sentiment_score":  score,
            "raw_score":        round((score + 1) * 5, 2),
            "collected_at":     collected_at,
        })

    rows = execute("SELECT COUNT(*) as c FROM signals")
    print(f"  Done — {rows[0]['c']} signals in database")

    # Summary
    tickers = execute("""
        SELECT ticker, COUNT(*) as n, ROUND(AVG(sentiment_score),3) as avg_s
        FROM signals GROUP BY ticker ORDER BY avg_s DESC
    """)
    print(f"\n  {'Ticker':<8} {'Signals':<10} Avg Sentiment")
    print(f"  {'-'*32}")
    for r in tickers:
        bar = "▲" if r["avg_s"] > 0.1 else ("▼" if r["avg_s"] < -0.1 else "—")
        print(f"  {r['ticker']:<8} {r['n']:<10} {r['avg_s']:>6}  {bar}")


if __name__ == "__main__":
    seed()
