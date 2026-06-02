"""
SMS notifications — instant alerts and rich daily 7am summary via Twilio.

Set in Railway Variables: TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN,
TWILIO_FROM_NUMBER, SMS_TO_NUMBER
"""

import json
import os
from datetime import datetime, timezone
from pathlib import Path
import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import insert, execute


def _get_twilio():
    try:
        from twilio.rest import Client
        sid   = os.getenv("TWILIO_ACCOUNT_SID", "")
        token = os.getenv("TWILIO_AUTH_TOKEN", "")
        if not sid or not token:
            raise ValueError("TWILIO credentials not set")
        return Client(sid, token)
    except ImportError:
        raise ImportError("Run: pip install twilio")


def _send_sms(body: str) -> bool:
    from_number = os.getenv("TWILIO_FROM_NUMBER", "")
    to_number   = os.getenv("SMS_TO_NUMBER", "")
    if not from_number or not to_number:
        print("[SMS] Twilio numbers not set — skipping")
        return False
    try:
        client  = _get_twilio()
        message = client.messages.create(body=body, from_=from_number, to=to_number)
        success = message.status in ("queued", "sent", "delivered")
        print(f"[SMS] → {to_number} {'✅' if success else '❌'} ({len(body)} chars)")
        return success
    except Exception as e:
        print(f"[SMS] Error: {e}")
        return False


def _get_price_str(ticker: str) -> str:
    try:
        from collectors.price_collector import format_price_line
        return format_price_line(ticker)
    except Exception:
        return ""


# ---------------------------------------------------------------------------
# Instant alert SMS
# ---------------------------------------------------------------------------

def send_alert_sms(ticker: str, score: float, risk_tier: str, reasoning: str) -> bool:
    from_number = os.getenv("TWILIO_FROM_NUMBER", "")
    to_number   = os.getenv("SMS_TO_NUMBER", "")
    if not from_number or not to_number:
        print("[SMS] Twilio numbers not set — skipping")
        return False

    # Live price
    price_str  = _get_price_str(ticker)
    price_part = f" | {price_str}" if price_str else ""

    # Top headlines driving this score
    headlines = execute("""
        SELECT content, source_detail, sentiment
        FROM signals
        WHERE ticker = ?
          AND collected_at >= datetime('now', '-24 hours')
          AND source IN ('news', 'reddit', 'sec_filing')
        ORDER BY
          CASE sentiment
            WHEN 'very_bullish' THEN 1
            WHEN 'bullish'      THEN 2
            ELSE 3
          END,
          collected_at DESC
        LIMIT 3
    """, (ticker,))

    # Active cascade events
    cascades = execute("""
        SELECT cascade_theme, reasoning
        FROM cascade_events
        WHERE detected_at >= datetime('now', '-48 hours')
          AND affected_tickers LIKE ?
        LIMIT 2
    """, (f'%{ticker}%',))

    # Insider trades
    insider = execute("""
        SELECT content FROM signals
        WHERE ticker = ? AND source = 'sec_filing'
          AND collected_at >= datetime('now', '-7 days')
        ORDER BY collected_at DESC LIMIT 1
    """, (ticker,))

    direction = "▲ BULLISH" if score >= 7 else ("→ NEUTRAL" if score >= 5 else "▼ BEARISH")
    lines = [
        f"⚡ StockPulse Alert",
        f"{ticker} — {score:.1f}/10 [{risk_tier} Risk] {direction}{price_part}",
        "",
    ]

    if reasoning:
        lines.append(reasoning[:200])
        lines.append("")

    if insider:
        lines.append(f"🏦 Insider: {insider[0]['content'][:120]}")
        lines.append("")

    if headlines:
        lines.append("📰 Key signals:")
        for h in headlines:
            src  = h.get("source_detail", "").replace("Google News: ", "").replace("Yahoo Finance: ", "")
            text = h.get("content", "")[:90]
            lines.append(f"• {text} [{src}]")
        lines.append("")

    if cascades:
        lines.append("🔗 Cascade:")
        for c in cascades:
            lines.append(f"• {c['cascade_theme']}: {c['reasoning'][:90]}")

    body    = "\n".join(lines)
    success = _send_sms(body)

    try:
        insert("alerts", {
            "ticker":           ticker,
            "alert_type":       "instant",
            "conviction_score": score,
            "risk_tier":        risk_tier,
            "trigger_reason":   reasoning or "High conviction score",
            "channels_sent":    json.dumps(["sms"]),
            "email_sent":       0,
            "sms_sent":         1 if success else 0,
            "sent_at":          datetime.now(timezone.utc).isoformat(),
        })
    except Exception:
        pass

    print(f"[SMS] {ticker} alert → {to_number} {'✅' if success else '❌'}")
    return success


# ---------------------------------------------------------------------------
# Daily summary SMS — 3 parts sent at 7am
# ---------------------------------------------------------------------------

def send_daily_sms_summary() -> bool:
    from_number = os.getenv("TWILIO_FROM_NUMBER", "")
    to_number   = os.getenv("SMS_TO_NUMBER", "")
    if not from_number or not to_number:
        print("[SMS] Twilio numbers not set — skipping daily summary")
        return False

    scores = execute("""
        SELECT ticker, conviction_score, risk_tier, reasoning,
               signal_count, reddit_score, news_score, cascade_score
        FROM daily_scores
        WHERE date = date('now')
        ORDER BY conviction_score DESC
        LIMIT 20
    """)
    if not scores:
        print("[SMS] No scores for today — skipping")
        return False

    now      = datetime.now()
    date_str = f"{now.strftime('%a %b')} {now.day}"  # e.g. "Mon Jun 2"
    avg_score = sum(s["conviction_score"] for s in scores) / len(scores)

    macro_signals = execute("""
        SELECT content, source_detail FROM signals
        WHERE ticker = 'MACRO' AND collected_at >= datetime('now', '-12 hours')
        ORDER BY collected_at DESC LIMIT 8
    """)

    cascades = execute("""
        SELECT cascade_theme, reasoning, affected_tickers FROM cascade_events
        WHERE detected_at >= datetime('now', '-12 hours')
        ORDER BY detected_at DESC LIMIT 5
    """)

    top_headlines = execute("""
        SELECT s.ticker, s.content, s.source_detail, s.sentiment
        FROM signals s
        WHERE s.collected_at >= datetime('now', '-12 hours')
          AND s.source IN ('news', 'sec_filing')
          AND s.sentiment IN ('very_bullish', 'bullish', 'very_bearish', 'bearish')
          AND s.ticker != 'MACRO'
        ORDER BY
          CASE s.sentiment WHEN 'very_bullish' THEN 1 WHEN 'very_bearish' THEN 2 ELSE 3 END,
          s.collected_at DESC
        LIMIT 8
    """)

    high_conviction = [s for s in scores if s["conviction_score"] >= 8.0]
    top_picks       = scores[:6]

    # ── PART 1: Market mood + world events ────────────────────────────────
    p1 = [f"📈 StockPulse — {date_str} (1/3)"]
    if avg_score >= 7:
        mood = "🟢 BULLISH — broad strength"
    elif avg_score >= 5.5:
        mood = "🟡 MIXED — selective opportunities"
    else:
        mood = "🔴 CAUTIOUS — stay defensive"
    p1.append(f"Market Mood: {mood}")
    p1.append(f"Avg conviction: {avg_score:.1f}/10 across {len(scores)} stocks")

    if macro_signals:
        p1.append("\n🌐 World Events Moving Markets:")
        seen = set()
        for m in macro_signals:
            content = m.get("content", "")
            headline = content.split("|", 1)[1].strip()[:90] if "|" in content else content[:90]
            if headline and headline not in seen:
                seen.add(headline)
                emoji = "▲" if "BULLISH" in content.upper() else ("▼" if "BEARISH" in content.upper() else "→")
                p1.append(f"{emoji} {headline}")

    if cascades:
        p1.append("\n🔗 Sector Cascades:")
        for c in cascades[:3]:
            try:
                affected = json.loads(c["affected_tickers"]) if isinstance(c["affected_tickers"], str) else c["affected_tickers"]
                p1.append(f"• {c['cascade_theme']} → {', '.join(affected[:4])}")
            except Exception:
                pass

    # ── PART 2: Picks + prices ────────────────────────────────────────────
    p2 = [f"⚡ StockPulse Picks — {date_str} (2/3)"]
    if high_conviction:
        p2.append("\n🔥 HIGH CONVICTION (8.0+/10):")
        for s in high_conviction:
            price_str = _get_price_str(s["ticker"])
            price_part = f" | {price_str}" if price_str else ""
            p2.append(f"\n{s['ticker']} — {s['conviction_score']:.1f}/10 [{s['risk_tier']}]{price_part}")
            if s.get("reasoning"):
                p2.append(s["reasoning"][:180])
    else:
        p2.append("\nNo high conviction picks today (threshold: 8.0)")

    p2.append("\n📊 Top 6 Watchlist:")
    for s in top_picks:
        bar       = "▲" if s["conviction_score"] >= 7 else ("→" if s["conviction_score"] >= 5 else "▼")
        price_str = _get_price_str(s["ticker"])
        price_part = f" {price_str}" if price_str else ""
        p2.append(f"  {bar} {s['ticker']:<5} {s['conviction_score']:.1f}/10{price_part}")

    # ── PART 3: Headlines + insider + opinion ─────────────────────────────
    p3 = [f"📰 StockPulse Analysis — {date_str} (3/3)"]

    # Insider trades from last 7 days
    insider_signals = execute("""
        SELECT ticker, content FROM signals
        WHERE source = 'sec_filing'
          AND collected_at >= datetime('now', '-7 days')
        ORDER BY raw_score DESC LIMIT 3
    """)
    if insider_signals:
        p3.append("\n🏦 Insider Activity:")
        for ins in insider_signals:
            p3.append(f"• [{ins['ticker']}] {ins['content'][:100]}")

    if top_headlines:
        p3.append("\n📰 Key Headlines:")
        seen = set()
        for h in top_headlines:
            text = h.get("content", "")[:90]
            if text in seen:
                continue
            seen.add(text)
            emoji = "▲" if "bullish" in h.get("sentiment", "") else "▼"
            p3.append(f"{emoji} [{h['ticker']}] {text}")

    p3.append("\n💡 StockPulse View:")
    for line in _build_opinion(scores, macro_signals, cascades, avg_score):
        p3.append(line)

    p3.append("\nNext scan in 30 min.")

    print("[SMS] Sending daily summary (3 parts)...")
    results = []
    for i, part in enumerate(["\n".join(p1), "\n".join(p2), "\n".join(p3)], 1):
        print(f"  Part {i}: {len(part)} chars")
        results.append(_send_sms(part))
    return all(results)


def _build_opinion(scores, macro_signals, cascades, avg_score) -> list[str]:
    lines = []
    high = [s for s in scores if s["conviction_score"] >= 8]
    weak = [s for s in scores if s["conviction_score"] < 4]

    if avg_score >= 7.5:
        lines.append("Broad bullish momentum across multiple sectors — classic risk-on environment. Consider adding to high-conviction positions.")
    elif avg_score >= 6:
        lines.append("Mixed but leaning positive. Focus on the 8.0+ names only — don't force trades on weaker signals.")
    else:
        lines.append("Weak signal environment. Hold cash or reduce sizes until conviction improves.")

    if high:
        lines.append(f"Strongest conviction: {', '.join(s['ticker'] for s in high[:3])}. Best signal-to-noise ratio right now.")

    macro_content = " ".join(m.get("content", "") for m in (macro_signals or [])).lower()

    if "hormuz" in macro_content or "suez" in macro_content or "red sea" in macro_content:
        lines.append("Shipping disruption detected — freight costs spike 2-4 weeks out. LMT, KTOS benefit; FDX, XPO face headwinds.")
    if "rate cut" in macro_content or "dovish" in macro_content:
        lines.append("Rate cut signals — bullish for ENPH, FSLR, ENVX, QS. Clean energy and battery plays could outperform.")
    if "export control" in macro_content or "chip export" in macro_content:
        lines.append("Chip export controls — near-term headwind for NVDA, AMD, MU. Watch dips as potential entry points.")
    if "spacex" in macro_content or ("space" in macro_content and "launch" in macro_content):
        lines.append("Space newsflow elevated — RKLB and ASTS move on SpaceX headlines even without direct involvement.")
    if "nuclear" in macro_content or "smr" in macro_content:
        lines.append("Nuclear energy momentum accelerating for data center power. OKLO, CEG, BWXT are primary beneficiaries.")
    if "fda" in macro_content:
        lines.append("FDA activity — check MRNA, CRSP, RXRX for individual setups as sector sentiment shifts.")
    if "insider" in macro_content or any("sec_filing" in (m.get("source_detail","")) for m in (macro_signals or [])):
        lines.append("Insider buying detected — one of the strongest conviction signals available. Check Part 3 for details.")

    if weak:
        lines.append(f"Weakest signals: {', '.join(s['ticker'] for s in weak[:2])}. Avoid new positions here.")

    return lines
