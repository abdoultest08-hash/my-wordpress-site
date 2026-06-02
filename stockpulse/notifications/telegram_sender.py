"""
Telegram notifications — replaces Twilio SMS.

Setup:
  1. Message @BotFather on Telegram → /newbot → get token
  2. Send any message TO your bot (so it knows your chat ID)
  3. Set in Railway Variables:
       TELEGRAM_BOT_TOKEN  = your bot token
       TELEGRAM_CHAT_ID    = auto-discovered on first run (see below)

On first startup, if TELEGRAM_CHAT_ID is not set, the bot will
print your chat ID in the Railway logs — copy it and add it as a variable.
"""

import json
import os
import sys
from datetime import datetime, timezone, timedelta, date
from pathlib import Path

import requests

sys.path.insert(0, str(Path(__file__).parent.parent))
from database.db import execute, insert, is_postgres

_API = "https://api.telegram.org/bot{token}/{method}"


def _token() -> str:
    t = os.getenv("TELEGRAM_BOT_TOKEN", "")
    if not t:
        raise ValueError("TELEGRAM_BOT_TOKEN not set in Railway Variables")
    return t


def _chat_id() -> str:
    cid = os.getenv("TELEGRAM_CHAT_ID", "")
    if cid:
        return cid
    # Auto-discover: fetch the most recent message sent to the bot
    try:
        resp = requests.get(
            _API.format(token=_token(), method="getUpdates"),
            timeout=10
        ).json()
        updates = resp.get("result", [])
        if updates:
            cid = str(updates[-1]["message"]["chat"]["id"])
            print(f"[Telegram] ✅ Auto-discovered TELEGRAM_CHAT_ID = {cid}")
            print(f"[Telegram] 👉 Add TELEGRAM_CHAT_ID={cid} to Railway Variables to skip this step")
            return cid
        else:
            print("[Telegram] ❌ No messages found — send any message to your bot first, then redeploy")
            return ""
    except Exception as e:
        print(f"[Telegram] Error discovering chat ID: {e}")
        return ""


def send_message(text: str, parse_mode: str = "HTML") -> bool:
    """Send a message to the configured Telegram chat."""
    token = _token()
    chat  = _chat_id()
    if not chat:
        return False
    try:
        resp = requests.post(
            _API.format(token=token, method="sendMessage"),
            json={"chat_id": chat, "text": text, "parse_mode": parse_mode},
            timeout=15
        )
        ok = resp.json().get("ok", False)
        print(f"[Telegram] Message sent {'✅' if ok else '❌'} ({len(text)} chars)")
        if not ok:
            print(f"[Telegram] Error: {resp.json().get('description')}")
        return ok
    except Exception as e:
        print(f"[Telegram] Error: {e}")
        return False


def _get_price_str(ticker: str) -> str:
    try:
        from collectors.price_collector import format_price_line
        return format_price_line(ticker)
    except Exception:
        return ""


# ---------------------------------------------------------------------------
# Instant alert
# ---------------------------------------------------------------------------

def send_alert(ticker: str, score: float, risk_tier: str, reasoning: str) -> bool:
    _now  = datetime.now(timezone.utc)
    _24h  = (_now - timedelta(hours=24)).isoformat()
    _48h  = (_now - timedelta(hours=48)).isoformat()
    _7d   = (_now - timedelta(days=7)).isoformat()

    price_str  = _get_price_str(ticker)
    price_part = f" | {price_str}" if price_str else ""

    headlines = execute("""
        SELECT content, source_detail, sentiment FROM signals
        WHERE ticker = ? AND collected_at >= ?
          AND source IN ('news', 'reddit', 'sec_filing')
        ORDER BY
          CASE sentiment WHEN 'very_bullish' THEN 1 WHEN 'bullish' THEN 2 ELSE 3 END,
          collected_at DESC LIMIT 3
    """, (ticker, _24h))

    cascades = execute("""
        SELECT cascade_theme, reasoning FROM cascade_events
        WHERE detected_at >= ? AND affected_tickers LIKE ? LIMIT 2
    """, (_48h, f'%{ticker}%'))

    insider = execute("""
        SELECT content FROM signals
        WHERE ticker = ? AND source = 'sec_filing' AND collected_at >= ?
        ORDER BY collected_at DESC LIMIT 1
    """, (ticker, _7d))

    direction = "▲ BULLISH" if score >= 7 else ("➡ NEUTRAL" if score >= 5 else "▼ BEARISH")

    lines = [
        f"<b>⚡ StockPulse Alert</b>",
        f"<b>{ticker}</b> — {score:.1f}/10 [{risk_tier} Risk] {direction}{price_part}",
        "",
    ]
    if reasoning:
        lines.append(reasoning[:300])
        lines.append("")
    if insider:
        lines.append(f"🏦 <b>Insider:</b> {insider[0]['content'][:150]}")
        lines.append("")
    if headlines:
        lines.append("📰 <b>Key signals:</b>")
        for h in headlines:
            src  = h.get("source_detail", "").replace("Google News: ", "").replace("Yahoo Finance: ", "")
            text = h.get("content", "")[:120]
            lines.append(f"• {text} [{src}]")
        lines.append("")
    if cascades:
        lines.append("🔗 <b>Cascade:</b>")
        for c in cascades:
            lines.append(f"• {c['cascade_theme']}: {c['reasoning'][:120]}")

    success = send_message("\n".join(lines))

    try:
        insert("alerts", {
            "ticker":           ticker,
            "alert_type":       "instant",
            "conviction_score": score,
            "risk_tier":        risk_tier,
            "trigger_reason":   reasoning or "High conviction score",
            "channels_sent":    json.dumps(["telegram"]),
            "email_sent":       0,
            "sms_sent":         1 if success else 0,
            "sent_at":          datetime.now(timezone.utc).isoformat(),
        })
    except Exception:
        pass

    return success


# ---------------------------------------------------------------------------
# Daily summary — single rich Telegram message (no 3-part SMS limit)
# ---------------------------------------------------------------------------

def send_daily_summary() -> bool:
    _today = date.today().isoformat()
    _12h   = (datetime.now(timezone.utc) - timedelta(hours=12)).isoformat()
    _7d    = (datetime.now(timezone.utc) - timedelta(days=7)).isoformat()

    scores = execute("""
        SELECT ticker, conviction_score, risk_tier, reasoning,
               signal_count, reddit_score, news_score, cascade_score
        FROM daily_scores WHERE date = ?
        ORDER BY conviction_score DESC LIMIT 20
    """, (_today,))

    print(f"[Telegram] Daily summary: {len(scores) if scores else 0} scores for {_today}")

    if not scores:
        scores = execute("""
            SELECT ticker, conviction_score, risk_tier, reasoning,
                   signal_count, reddit_score, news_score, cascade_score
            FROM daily_scores
            ORDER BY date DESC, conviction_score DESC LIMIT 20
        """)
        print(f"[Telegram] Fallback: {len(scores) if scores else 0} scores in DB")

    if not scores:
        send_message("⚠️ <b>StockPulse:</b> No scores yet — pipeline still running. Try again in a few minutes.")
        return False

    now      = datetime.now()
    date_str = f"{now.strftime('%a %b')} {now.day}"
    avg_score = sum(s["conviction_score"] for s in scores) / len(scores)

    macro_signals = execute("""
        SELECT content, source_detail FROM signals
        WHERE ticker = 'MACRO' AND collected_at >= ?
        ORDER BY collected_at DESC LIMIT 6
    """, (_12h,))

    cascades = execute("""
        SELECT cascade_theme, reasoning, affected_tickers FROM cascade_events
        WHERE detected_at >= ? ORDER BY detected_at DESC LIMIT 4
    """, (_12h,))

    top_headlines = execute("""
        SELECT s.ticker, s.content, s.sentiment FROM signals s
        WHERE s.collected_at >= ?
          AND s.source IN ('news', 'sec_filing')
          AND s.sentiment IN ('very_bullish', 'bullish', 'very_bearish', 'bearish')
          AND s.ticker != 'MACRO'
        ORDER BY
          CASE s.sentiment WHEN 'very_bullish' THEN 1 WHEN 'very_bearish' THEN 2 ELSE 3 END,
          s.collected_at DESC LIMIT 6
    """, (_12h,))

    insider_signals = execute("""
        SELECT ticker, content FROM signals
        WHERE source = 'sec_filing' AND collected_at >= ?
        ORDER BY raw_score DESC LIMIT 3
    """, (_7d,))

    high_conviction = [s for s in scores if s["conviction_score"] >= 8.0]
    top_picks       = scores[:6]

    # Market mood
    if avg_score >= 7:
        mood = "🟢 BULLISH — broad strength"
    elif avg_score >= 5.5:
        mood = "🟡 MIXED — selective opportunities"
    else:
        mood = "🔴 CAUTIOUS — stay defensive"

    lines = [
        f"<b>📈 StockPulse Morning Brief — {date_str}</b>",
        f"Market: {mood}  |  Avg conviction: {avg_score:.1f}/10",
        "",
    ]

    # World events
    if macro_signals:
        lines.append("🌐 <b>World Events:</b>")
        seen = set()
        for m in macro_signals:
            content = m.get("content", "")
            headline = content.split("|", 1)[1].strip()[:100] if "|" in content else content[:100]
            if headline and headline not in seen:
                seen.add(headline)
                arrow = "▲" if "BULLISH" in content.upper() else ("▼" if "BEARISH" in content.upper() else "➡")
                lines.append(f"{arrow} {headline}")
        lines.append("")

    # Sector cascades
    if cascades:
        lines.append("🔗 <b>Sector Cascades:</b>")
        for c in cascades[:3]:
            try:
                affected = json.loads(c["affected_tickers"]) if isinstance(c["affected_tickers"], str) else c["affected_tickers"]
                lines.append(f"• {c['cascade_theme']} → {', '.join(affected[:4])}")
            except Exception:
                pass
        lines.append("")

    # High conviction picks
    if high_conviction:
        lines.append("🔥 <b>HIGH CONVICTION (8.0+/10):</b>")
        for s in high_conviction:
            price_str  = _get_price_str(s["ticker"])
            price_part = f" | {price_str}" if price_str else ""
            lines.append(f"\n<b>{s['ticker']}</b> — {s['conviction_score']:.1f}/10 [{s['risk_tier']}]{price_part}")
            if s.get("reasoning"):
                lines.append(s["reasoning"][:200])
    else:
        lines.append("No high conviction picks today (threshold: 8.0+/10)")
    lines.append("")

    # Top 6 watchlist
    lines.append("📊 <b>Top 6 Watchlist:</b>")
    for s in top_picks:
        bar       = "▲" if s["conviction_score"] >= 7 else ("➡" if s["conviction_score"] >= 5 else "▼")
        price_str = _get_price_str(s["ticker"])
        price_part = f" {price_str}" if price_str else ""
        lines.append(f"  {bar} <b>{s['ticker']}</b>  {s['conviction_score']:.1f}/10{price_part}")
    lines.append("")

    # Insider activity
    if insider_signals:
        lines.append("🏦 <b>Insider Activity:</b>")
        for ins in insider_signals:
            lines.append(f"• [{ins['ticker']}] {ins['content'][:120]}")
        lines.append("")

    # Key headlines
    if top_headlines:
        lines.append("📰 <b>Key Headlines:</b>")
        seen = set()
        for h in top_headlines:
            text = h.get("content", "")[:110]
            if text in seen:
                continue
            seen.add(text)
            arrow = "▲" if "bullish" in h.get("sentiment", "") else "▼"
            lines.append(f"{arrow} [{h['ticker']}] {text}")
        lines.append("")

    # Opinion
    lines.append("💡 <b>StockPulse View:</b>")
    for line in _build_opinion(scores, macro_signals, cascades, avg_score):
        lines.append(line)

    lines.append("")
    lines.append("🔄 Next scan in 30 min  |  Reply <b>update</b> for on-demand refresh")

    return send_message("\n".join(lines))


def _build_opinion(scores, macro_signals, cascades, avg_score) -> list[str]:
    lines = []
    high = [s for s in scores if s["conviction_score"] >= 8]
    weak = [s for s in scores if s["conviction_score"] < 4]

    if avg_score >= 7.5:
        lines.append("Broad bullish momentum — classic risk-on environment. Consider adding to high-conviction positions.")
    elif avg_score >= 6:
        lines.append("Mixed but leaning positive. Focus on the 8.0+ names only — don't force trades on weaker signals.")
    else:
        lines.append("Weak signal environment. Hold cash or reduce sizes until conviction improves.")

    if high:
        lines.append(f"Strongest: {', '.join(s['ticker'] for s in high[:3])} — best signal-to-noise right now.")

    macro_content = " ".join(m.get("content", "") for m in (macro_signals or [])).lower()
    if "rate cut" in macro_content or "dovish" in macro_content:
        lines.append("Rate cut signals — bullish for ENPH, FSLR, ENVX, QS.")
    if "export control" in macro_content or "chip export" in macro_content:
        lines.append("Chip export controls — near-term headwind for NVDA, AMD, MU. Watch dips.")
    if "nuclear" in macro_content or "smr" in macro_content:
        lines.append("Nuclear momentum building for data center power — OKLO, CEG, BWXT.")
    if "hormuz" in macro_content or "red sea" in macro_content:
        lines.append("Shipping disruption — LMT, KTOS benefit; FDX, XPO face headwinds.")
    if weak:
        lines.append(f"Avoid: {', '.join(s['ticker'] for s in weak[:2])} — weakest signals today.")

    return lines
