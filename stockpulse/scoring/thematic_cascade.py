"""
Thematic cascade mapper.

Detects when a catalyst event (news about a major company or theme)
indirectly affects related tickers in your watchlist.

Example:
  "SpaceX announces IPO plans"
    → RKLB (competitor/comparable)  proximity: 0.85
    → ASTS (space ecosystem)         proximity: 0.60
    → IRDM (satellite sector halo)   proximity: 0.55

Cascade rules are defined below as a list of patterns.
Each pattern has: keywords to match, affected tickers, proximity scores, and a theme label.
"""

import json
import re
from datetime import datetime, timezone
from pathlib import Path

import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

# ---------------------------------------------------------------------------
# Cascade rules — the intelligence of the system
# Add new rules here as you discover new thematic relationships.
# ---------------------------------------------------------------------------

CASCADE_RULES = [
    # ── SPACE ──────────────────────────────────────────────────────────────
    {
        "theme": "Space ecosystem halo",
        "keywords": ["spacex", "starship", "falcon", "space launch", "spacex ipo"],
        "affected": {"RKLB": 0.85, "ASTS": 0.60, "IRDM": 0.55, "LMT": 0.40},
        "reasoning": "SpaceX news raises investor attention across the entire commercial space sector.",
    },
    {
        "theme": "Satellite broadband expansion",
        "keywords": ["starlink", "satellite internet", "low earth orbit broadband", "leo constellation"],
        "affected": {"ASTS": 0.90, "IRDM": 0.70, "RKLB": 0.50},
        "reasoning": "Satellite broadband news directly benefits satellite connectivity players.",
    },
    {
        "theme": "Defense budget expansion",
        "keywords": ["defense budget", "nato spending", "military contract", "pentagon budget", "defense appropriations"],
        "affected": {"LMT": 0.85, "PLTR": 0.75, "RKLB": 0.60},
        "reasoning": "Increased defense spending benefits contractors and AI/analytics defense suppliers.",
    },

    # ── AI / SEMICONDUCTORS ────────────────────────────────────────────────
    {
        "theme": "AI infrastructure buildout",
        "keywords": ["ai datacenter", "gpu demand", "ai capital expenditure", "hyperscaler capex", "ai infrastructure"],
        "affected": {"NVDA": 0.95, "AMD": 0.80, "SMCI": 0.75, "ARM": 0.65},
        "reasoning": "AI infrastructure spending directly drives GPU and server demand.",
    },
    {
        "theme": "AI chip architecture royalties",
        "keywords": ["arm architecture", "arm chip", "arm ipo", "arm license", "risc-v competition"],
        "affected": {"ARM": 0.90, "NVDA": 0.40, "AMD": 0.40},
        "reasoning": "ARM architecture adoption news flows directly to royalty revenue.",
    },
    {
        "theme": "Semiconductor supply chain",
        "keywords": ["tsmc", "chip shortage", "semiconductor supply", "fab capacity", "chip act"],
        "affected": {"NVDA": 0.70, "AMD": 0.70, "ARM": 0.50, "SMCI": 0.45},
        "reasoning": "Supply chain news affects all fabless semiconductor companies.",
    },
    {
        "theme": "Microsoft AI spending",
        "keywords": ["microsoft azure", "microsoft ai", "openai investment", "copilot expansion"],
        "affected": {"NVDA": 0.80, "AMD": 0.55, "PLTR": 0.50, "ARM": 0.40},
        "reasoning": "Microsoft AI capex flows directly to GPU suppliers and AI analytics platforms.",
    },

    # ── ENERGY / CLEAN TECH ────────────────────────────────────────────────
    {
        "theme": "Solar policy tailwind",
        "keywords": ["ira tax credit", "solar subsidy", "clean energy incentive", "inflation reduction act solar"],
        "affected": {"ENPH": 0.85, "FSLR": 0.85},
        "reasoning": "Solar subsidies directly improve unit economics for residential and utility solar.",
    },
    {
        "theme": "Interest rate impact on solar",
        "keywords": ["interest rate hike", "fed rate", "mortgage rate rise", "rate increase"],
        "affected": {"ENPH": -0.70, "FSLR": -0.40, "PLUG": -0.50},
        "reasoning": "Higher rates suppress residential solar financing, hurting installers and equipment makers.",
    },
    {
        "theme": "EV adoption acceleration",
        "keywords": ["ev sales record", "electric vehicle growth", "ev adoption", "ev charging infrastructure"],
        "affected": {"TSLA": 0.75, "ENPH": 0.40, "PLUG": 0.50},
        "reasoning": "EV growth news benefits Tesla and adjacent clean energy infrastructure plays.",
    },
    {
        "theme": "Hydrogen economy",
        "keywords": ["hydrogen fuel cell", "green hydrogen", "hydrogen economy", "electrolyzer"],
        "affected": {"PLUG": 0.90, "ENPH": 0.30},
        "reasoning": "Hydrogen policy and funding news directly impacts fuel cell manufacturers.",
    },

    # ── CROSS-SECTOR ───────────────────────────────────────────────────────
    {
        "theme": "AI defense convergence",
        "keywords": ["ai military", "autonomous weapons", "ai surveillance", "defense ai contract"],
        "affected": {"PLTR": 0.90, "LMT": 0.60, "RKLB": 0.40, "NVDA": 0.50},
        "reasoning": "AI defense convergence uniquely benefits Palantir and primes with AI integration.",
    },
    {
        "theme": "Mega-cap tech earnings halo",
        "keywords": ["microsoft earnings", "google earnings", "meta earnings", "amazon earnings", "apple earnings"],
        "affected": {"NVDA": 0.65, "AMD": 0.55, "ARM": 0.60, "PLTR": 0.40},
        "reasoning": "Strong mega-cap tech earnings signal healthy AI/cloud spending downstream.",
    },
]

# ---------------------------------------------------------------------------
# Detector
# ---------------------------------------------------------------------------

def _match_rule(text: str, rule: dict) -> bool:
    """Return True if any keyword from the rule appears in the text."""
    text_lower = text.lower()
    return any(kw in text_lower for kw in rule["keywords"])


def detect_cascades(signals: list[dict] | None = None) -> int:
    """
    Scan recent news/reddit signals for cascade triggers.
    Saves detected cascade events to the database.
    Returns the number of new cascade events detected.
    """
    if signals is None:
        from datetime import timedelta
        cutoff = (datetime.now(timezone.utc) - timedelta(hours=24)).isoformat()
        signals = execute(
            "SELECT * FROM signals WHERE collected_at >= ? AND source IN ('news','ceo_social')",
            (cutoff,)
        )

    if not signals:
        return 0

    detected = 0

    for signal in signals:
        content = signal.get("content", "")

        for rule in CASCADE_RULES:
            if not _match_rule(content, rule):
                continue

            # Filter affected tickers to only those in your watchlist
            watchlist_symbols = {
                r["symbol"] for r in execute(
                    "SELECT symbol FROM tickers WHERE watchlist_status IN ('active','watching')"
                )
            }

            affected_in_watchlist = {
                sym: prox for sym, prox in rule["affected"].items()
                if sym in watchlist_symbols
            }

            if not affected_in_watchlist:
                continue

            # Don't create duplicate cascade events for the same signal + rule
            existing = execute(
                "SELECT id FROM cascade_events WHERE catalyst_headline = ? AND cascade_theme = ?",
                (signal["content"][:200], rule["theme"])
            )
            if existing:
                continue

            # Determine catalyst ticker (the ticker in the signal, if any)
            catalyst_ticker = signal.get("ticker")

            insert("cascade_events", {
                "catalyst_ticker":   catalyst_ticker,
                "catalyst_headline": signal["content"][:200],
                "catalyst_source":   signal.get("source_detail", signal.get("source")),
                "affected_tickers":  json.dumps(list(affected_in_watchlist.keys())),
                "proximity_scores":  json.dumps(affected_in_watchlist),
                "cascade_theme":     rule["theme"],
                "reasoning":         rule["reasoning"],
                "detected_at":       datetime.now(timezone.utc).isoformat(),
            })
            detected += 1

            print(f"  [Cascade] '{rule['theme']}' detected")
            print(f"    Catalyst: {signal['content'][:80]}...")
            print(f"    Affects: {', '.join(f'{s}({p})' for s,p in affected_in_watchlist.items())}")

    return detected


# ---------------------------------------------------------------------------
# Run standalone for testing
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    print("Running cascade detector on mock data...\n")

    # Inject a test signal that should trigger cascades
    from database.db import insert as db_insert
    test_signals = [
        {
            "content": "SpaceX announces IPO plans, valuation expected at $200B, Starship fully operational",
            "source": "news", "source_detail": "Reuters", "ticker": None,
            "collected_at": datetime.now(timezone.utc).isoformat(),
        },
        {
            "content": "Microsoft Azure AI capex to increase 40% next year, massive GPU procurement expected",
            "source": "news", "source_detail": "CNBC", "ticker": "MSFT",
            "collected_at": datetime.now(timezone.utc).isoformat(),
        },
        {
            "content": "Federal Reserve raises interest rates by 50bps, mortgage rates hit 8%",
            "source": "news", "source_detail": "Reuters", "ticker": None,
            "collected_at": datetime.now(timezone.utc).isoformat(),
        },
    ]

    n = detect_cascades(test_signals)
    print(f"\n{n} cascade event(s) detected and saved")

    events = execute("SELECT cascade_theme, affected_tickers, proximity_scores, reasoning FROM cascade_events")
    if events:
        print(f"\nAll cascade events in database ({len(events)}):")
        for e in events:
            affected = json.loads(e["affected_tickers"])
            scores   = json.loads(e["proximity_scores"])
            print(f"\n  Theme: {e['cascade_theme']}")
            print(f"  Affected: {', '.join(f'{s} ({scores[s]:.0%})' for s in affected)}")
            print(f"  Why: {e['reasoning']}")
