""" 
Macro news collector — tracks world events that affect entire industries.

Monitors geopolitical, economic, and tech-industry events such as:
  - Hormuz / Suez / Red Sea shipping disruptions
  - Fed interest rate decisions
  - China/Taiwan tensions and chip export controls
  - AI industry moves (GPU allocations, model launches, policy)
  - Oil price shocks
  - Nuclear energy policy
  - Global supply chain disruptions

These events are stored as macro signals and used to:
  1. Enrich the daily SMS summary with a world-events section
  2. Trigger industry-level cascade boosts/penalties
"""

import time
from datetime import datetime, timezone
from pathlib import Path

import feedparser
import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

MACRO_FEEDS = [
    {"name": "Reuters: World News", "url": "https://feeds.reuters.com/reuters/worldNews", "theme": "geopolitical"},
    {"name": "Reuters: Business", "url": "https://feeds.reuters.com/reuters/businessNews", "theme": "macro_economy"},
    {"name": "Google News: Shipping & Trade", "url": "https://news.google.com/rss/search?q=hormuz+suez+red+sea+shipping+strait+cargo&hl=en-US&gl=US&ceid=US:en", "theme": "shipping_trade"},
    {"name": "Google News: Fed & Interest Rates", "url": "https://news.google.com/rss/search?q=federal+reserve+interest+rate+inflation+CPI&hl=en-US&gl=US&ceid=US:en", "theme": "macro_economy"},
    {"name": "Google News: China Tech & Chips", "url": "https://news.google.com/rss/search?q=china+chip+export+control+taiwan+semiconductor&hl=en-US&gl=US&ceid=US:en", "theme": "geopolitical"},
    {"name": "Google News: AI Industry", "url": "https://news.google.com/rss/search?q=openai+anthropic+google+deepmind+gpu+AI+industry&hl=en-US&gl=US&ceid=US:en", "theme": "ai_industry"},
    {"name": "Google News: Energy & Oil", "url": "https://news.google.com/rss/search?q=oil+price+OPEC+energy+crisis+natural+gas&hl=en-US&gl=US&ceid=US:en", "theme": "energy"},
    {"name": "Google News: Data Centers & Power", "url": "https://news.google.com/rss/search?q=data+center+power+grid+nuclear+energy+hyperscaler&hl=en-US&gl=US&ceid=US:en", "theme": "data_center"},
    {"name": "Google News: Biotech & FDA", "url": "https://news.google.com/rss/search?q=FDA+approval+drug+trial+biotech+pharma&hl=en-US&gl=US&ceid=US:en", "theme": "biotech"},
    {"name": "Google News: Logistics & Supply Chain", "url": "https://news.google.com/rss/search?q=supply+chain+logistics+freight+shipping+port&hl=en-US&gl=US&ceid=US:en", "theme": "logistics"},
]

MACRO_PATTERNS = [
    {"keywords": ["hormuz", "strait of hormuz", "persian gulf", "iran oil"], "theme": "shipping_trade", "industries_affected": ["Energy / Clean Tech", "Space / Defense / Aerospace"], "direction": "bearish", "summary": "Hormuz Strait tension — oil supply risk, defense spending likely rises"},
    {"keywords": ["suez canal", "red sea", "houthi", "shipping disruption", "cargo delay"], "theme": "shipping_trade", "industries_affected": ["Logistics", "Energy / Clean Tech"], "direction": "bearish", "summary": "Shipping route disruption — logistics costs spike, delivery delays"},
    {"keywords": ["federal reserve", "fed rate", "interest rate decision", "fomc", "rate hike", "rate cut"], "theme": "macro_economy", "industries_affected": ["Energy / Clean Tech", "Biotech", "Tech / AI / Semiconductors"], "direction": "mixed", "summary": "Fed rate decision — rate cuts bullish for growth/clean energy, hikes bearish"},
    {"keywords": ["chip export", "semiconductor export ban", "china export control", "huawei ban", "entity list"], "theme": "geopolitical", "industries_affected": ["Tech / AI / Semiconductors"], "direction": "bearish", "summary": "US chip export controls — reduces addressable market for semiconductor companies"},
    {"keywords": ["taiwan", "taiwan strait", "taiwan invasion", "china taiwan", "tsmc taiwan"], "theme": "geopolitical", "industries_affected": ["Tech / AI / Semiconductors", "Space / Defense / Aerospace"], "direction": "bearish", "summary": "Taiwan tensions — semiconductor supply chain risk, defense spending rises"},
    {"keywords": ["gpu allocation", "gpu supply", "nvidia gpu", "compute allocation", "h100", "b200", "blackwell"], "theme": "ai_industry", "industries_affected": ["Tech / AI / Semiconductors", "Data Centers"], "direction": "bullish", "summary": "GPU demand/supply news — directly impacts AI infrastructure buildout pace"},
    {"keywords": ["openai", "anthropic", "google gemini", "grok", "llm", "foundation model", "ai model launch"], "theme": "ai_industry", "industries_affected": ["Tech / AI / Semiconductors", "Data Centers"], "direction": "bullish", "summary": "AI model advancement — signals continued GPU and data center demand"},
    {"keywords": ["nuclear power", "nuclear plant", "small modular reactor", "smr", "nuclear energy data center"], "theme": "data_center", "industries_affected": ["Data Centers", "Energy / Clean Tech"], "direction": "bullish", "summary": "Nuclear energy expansion — powers next-gen data centers, clean energy beneficiary"},
    {"keywords": ["data center construction", "hyperscaler", "cloud capex", "microsoft data center", "amazon aws", "google cloud build"], "theme": "data_center", "industries_affected": ["Data Centers", "Tech / AI / Semiconductors"], "direction": "bullish", "summary": "Hyperscaler data center buildout — benefits cooling, power, and chip suppliers"},
    {"keywords": ["fda approval", "fda granted", "drug approval", "clinical trial success", "phase 3"], "theme": "biotech", "industries_affected": ["Biotech"], "direction": "bullish", "summary": "FDA approval or positive trial — sector-wide sentiment lift for biotech"},
    {"keywords": ["fda rejection", "clinical trial failure", "phase 3 failure", "drug withdrawn"], "theme": "biotech", "industries_affected": ["Biotech"], "direction": "bearish", "summary": "FDA rejection or trial failure — biotech sentiment dampened"},
    {"keywords": ["oil price", "crude oil", "brent crude", "opec cut", "opec production"], "theme": "energy", "industries_affected": ["Energy / Clean Tech", "Logistics"], "direction": "mixed", "summary": "Oil price movement — high oil lifts energy stocks but pressures logistics/transport"},
    {"keywords": ["defense contract", "military spending", "nato", "pentagon", "war", "conflict escalation"], "theme": "geopolitical", "industries_affected": ["Space / Defense / Aerospace"], "direction": "bullish", "summary": "Geopolitical escalation — defense and aerospace spending accelerates"},
    {"keywords": ["battery breakthrough", "solid state battery", "lithium", "ev battery", "battery storage"], "theme": "energy", "industries_affected": ["Energy / Clean Tech", "Electric Batteries"], "direction": "bullish", "summary": "Battery technology advance — benefits EV adoption and grid storage"},
    {"keywords": ["recession", "gdp contraction", "economic slowdown", "unemployment rise", "layoffs surge"], "theme": "macro_economy", "industries_affected": ["Tech / AI / Semiconductors", "Biotech", "Logistics"], "direction": "bearish", "summary": "Recession risk — broad market pressure, growth stocks most affected"},
]

REQUEST_DELAY = 1.5


def _match_patterns(text: str) -> list[dict]:
    text_lower = text.lower()
    return [p for p in MACRO_PATTERNS if any(kw in text_lower for kw in p["keywords"])]


def collect() -> list[dict]:
    print(f"[Macro] Starting macro news collection — {len(MACRO_FEEDS)} feeds")
    events = []
    seen_urls = set()
    seen_summaries = set()

    for feed_cfg in MACRO_FEEDS:
        try:
            feed = feedparser.parse(feed_cfg["url"])
            for entry in feed.get("entries", []):
                url = entry.get("link", "")
                if url in seen_urls:
                    continue
                seen_urls.add(url)

                title = entry.get("title", "")
                text  = f"{title} {entry.get('summary', '')}".strip()

                for pattern in _match_patterns(text):
                    key = f"{pattern['theme']}:{title[:80]}"
                    if key in seen_summaries:
                        continue
                    seen_summaries.add(key)

                    events.append({
                        "headline":            title[:200],
                        "url":                 url,
                        "theme":               pattern["theme"],
                        "industries_affected": pattern["industries_affected"],
                        "direction":           pattern["direction"],
                        "summary":             pattern["summary"],
                        "source":              feed_cfg["name"],
                        "collected_at":        datetime.now(timezone.utc).isoformat(),
                    })

                    try:
                        insert("signals", {
                            "ticker":              "MACRO",
                            "source":              "news",
                            "source_detail":       f"Macro:{pattern['theme']}",
                            "content":             f"[{pattern['direction'].upper()}] {pattern['summary']} | {title[:150]}",
                            "url":                 url,
                            "sentiment":           "bullish" if pattern["direction"] == "bullish" else ("bearish" if pattern["direction"] == "bearish" else "neutral"),
                            "sentiment_score":     0.3 if pattern["direction"] == "bullish" else (-0.3 if pattern["direction"] == "bearish" else 0.0),
                            "raw_score":           6.5 if pattern["direction"] == "bullish" else (3.5 if pattern["direction"] == "bearish" else 5.0),
                            "collected_at":        datetime.now(timezone.utc).isoformat(),
                            "source_published_at": None,
                        })
                    except Exception:
                        pass

        except Exception as e:
            print(f"  [Macro] Error fetching {feed_cfg['name']}: {e}")

        time.sleep(REQUEST_DELAY)

    print(f"[Macro] Done — {len(events)} macro event(s) identified")
    return events
