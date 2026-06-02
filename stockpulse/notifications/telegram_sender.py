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

import html
import json
import os
import re
import sys
from datetime import datetime, timezone, timedelta, date
from pathlib import Path

import requests


def _clean(text: str, limit: int = 0) -> str:
    """Strip HTML tags and escape special chars for Telegram HTML mode."""
    text = re.sub(r'<[^>]+>', '', str(text))   # strip any HTML tags
    text = html.escape(text)                    # escape & < > "
    return text[:limit] if limit else text

sys.path.insert(0, str(Path(__file__).parent.parent))
from database.db import execute, insert, is_postgres

_API = "https://api.telegram.org/bot{token}/{method}"

# Cached at runtime by the polling loop — no env var needed
_runtime_chat_id: str = ""


def set_chat_id(cid: str):
    """Called by the polling loop the moment a message arrives."""
    global _runtime_chat_id
    if _runtime_chat_id != str(cid):
        _runtime_chat_id = str(cid)
        print(f"[Telegram] Chat ID set: {cid}  👉 Add TELEGRAM_CHAT_ID={cid} to Railway Variables")


def _token() -> str:
    t = os.getenv("TELEGRAM_BOT_TOKEN", "")
    if not t:
        raise ValueError("TELEGRAM_BOT_TOKEN not set in Railway Variables")
    return t


def _chat_id() -> str:
    # 1. Env var (persistent across restarts)
    cid = os.getenv("TELEGRAM_CHAT_ID", "")
    if cid:
        return cid
    # 2. Runtime cache set by polling loop
    if _runtime_chat_id:
        return _runtime_chat_id
    print("[Telegram] TELEGRAM_CHAT_ID not set and no message received yet — send any message to your bot")
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
        lines.append(_clean(reasoning, 300))
        lines.append("")
    if insider:
        lines.append(f"🏦 <b>Insider:</b> {_clean(insider[0]['content'], 150)}")
        lines.append("")
    if headlines:
        lines.append("📰 <b>Key signals:</b>")
        for h in headlines:
            src  = _clean(h.get("source_detail", "").replace("Google News: ", "").replace("Yahoo Finance: ", ""))
            text = _clean(h.get("content", ""), 120)
            lines.append(f"• {text} [{src}]")
        lines.append("")
    if cascades:
        lines.append("🔗 <b>Cascade:</b>")
        for c in cascades:
            lines.append(f"• {_clean(c['cascade_theme'])}: {_clean(c['reasoning'], 120)}")

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
        f"<b>📈 StockPulse — {date_str}</b>",
        f"{mood}  |  Avg: {avg_score:.1f}/10 across {len(scores)} stocks",
        "",
    ]

    # World events — max 4, clean title only (no source repetition)
    if macro_signals:
        lines.append("🌐 <b>World Events:</b>")
        seen_headlines = set()
        count = 0
        for m in macro_signals:
            if count >= 4:
                break
            content = m.get("content", "")
            # Extract headline before source separator
            headline = content.split("|", 1)[0].strip() if "|" in content else content
            # Strip sentiment prefix like [BULLISH]
            headline = re.sub(r'^\[.*?\]\s*', '', headline).strip()
            headline = _clean(headline, 90)
            if not headline or headline in seen_headlines:
                continue
            seen_headlines.add(headline)
            arrow = "▲" if "BULLISH" in content.upper() else ("▼" if "BEARISH" in content.upper() else "➡")
            lines.append(f"  {arrow} {headline}")
            count += 1
        lines.append("")

    # Active themes — deduplicated by theme name
    if cascades:
        lines.append("🔗 <b>Active Themes:</b>")
        seen_themes = set()
        for c in cascades:
            theme = c.get("cascade_theme", "")
            if theme in seen_themes:
                continue
            seen_themes.add(theme)
            try:
                affected = json.loads(c["affected_tickers"]) if isinstance(c["affected_tickers"], str) else c["affected_tickers"]
                lines.append(f"  • {_clean(theme)} → {', '.join(affected[:4])}")
            except Exception:
                pass
        lines.append("")

    # High conviction picks
    if high_conviction:
        lines.append("🔥 <b>HIGH CONVICTION (8.0+):</b>")
        for s in high_conviction:
            price_str  = _get_price_str(s["ticker"])
            price_part = f"  {price_str}" if price_str else ""
            risk_emoji = {"Low": "🔵", "Medium": "🟡", "High": "🟠", "Speculative": "🔴"}.get(s["risk_tier"], "⚪")
            lines.append(f"\n{risk_emoji} <b>{s['ticker']}</b>  {s['conviction_score']:.1f}/10{price_part}")
            if s.get("reasoning"):
                sentences = [x.strip() for x in _clean(s["reasoning"]).split(".") if x.strip()]
                lines.append(". ".join(sentences[:2]) + ".")
    else:
        lines.append("No high conviction picks today (threshold: 8.0+/10)")
    lines.append("")

    # Watchlist with prices
    lines.append("📊 <b>Watchlist:</b>")
    for s in top_picks:
        bar        = "▲" if s["conviction_score"] >= 7 else ("➡" if s["conviction_score"] >= 5 else "▼")
        price_str  = _get_price_str(s["ticker"])
        price_part = f"  <i>{price_str}</i>" if price_str else ""
        lines.append(f"  {bar} <b>{s['ticker']}</b>  {s['conviction_score']:.1f}/10{price_part}")
    lines.append("")

    # Insider activity
    if insider_signals:
        lines.append("🏦 <b>Insider Moves:</b>")
        for ins in insider_signals:
            lines.append(f"  • <b>{ins['ticker']}</b>  {_clean(ins['content'], 100)}")
        lines.append("")

    # Headlines — one per ticker, clean title, no source duplication
    if top_headlines:
        lines.append("📰 <b>Top Headlines:</b>")
        seen_tickers = set()
        seen_texts   = set()
        for h in top_headlines:
            tkr   = h["ticker"]
            raw   = h.get("content", "")
            title = re.sub(r'\s*-\s*\S[\S ]{0,30}$', '', raw).strip()
            title = _clean(title, 95)
            if not title or title in seen_texts or tkr in seen_tickers:
                continue
            seen_texts.add(title)
            seen_tickers.add(tkr)
            arrow = "▲" if "bullish" in h.get("sentiment", "") else "▼"
            lines.append(f"  {arrow} <b>{tkr}</b>  {title}")
        lines.append("")

    # View
    lines.append("💡 <b>View:</b>")
    for line in _build_opinion(scores, macro_signals, cascades, avg_score):
        lines.append(f"  {_clean(line)}")

    lines.append("")
    lines.append("<i>Next scan in 30 min — msg <b>update</b> anytime</i>")

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
