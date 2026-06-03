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


def _get_price_data(ticker: str) -> dict | None:
    try:
        from collectors.price_collector import get_price, _fetch_finnhub, _fetch_yfinance
        # Try DB first (fastest)
        p = get_price(ticker)
        if p and float(p.get("price", 0)) > 0:
            return p
        # DB miss — fetch live right now
        api_key = os.getenv("FINNHUB_API_KEY", "")
        if api_key:
            p = _fetch_finnhub(ticker, api_key)
            if p:
                return p
        return _fetch_yfinance(ticker)
    except Exception:
        return None


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
    _24h   = (datetime.now(timezone.utc) - timedelta(hours=24)).isoformat()
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
            FROM daily_scores ORDER BY date DESC, conviction_score DESC LIMIT 20
        """)
        print(f"[Telegram] Fallback: {len(scores) if scores else 0} scores in DB")

    if not scores:
        send_message("⚠️ <b>StockPulse:</b> No scores yet — pipeline still running. Try again in a few minutes.")
        return False

    now       = datetime.now(timezone.utc)
    date_str  = f"{now.strftime('%a %b')} {now.day}"
    time_str  = now.strftime("%H:%M UTC")
    avg_score = sum(s["conviction_score"] for s in scores) / len(scores)

    # Fetch all data
    macro_signals = execute("""
        SELECT content, source_detail FROM signals
        WHERE ticker = 'MACRO' AND collected_at >= ?
        ORDER BY collected_at DESC LIMIT 12
    """, (_12h,))

    cascades = execute("""
        SELECT DISTINCT cascade_theme, reasoning, affected_tickers FROM cascade_events
        WHERE detected_at >= ? ORDER BY detected_at DESC LIMIT 20
    """, (_12h,))

    executive_signals = execute("""
        SELECT s.ticker, s.content, s.source_detail, s.sentiment FROM signals s
        WHERE s.collected_at >= ?
          AND s.source = 'executive'
        ORDER BY s.raw_score DESC, s.collected_at DESC LIMIT 10
    """, (_24h,))

    corporate_news = execute("""
        SELECT s.ticker, s.content, s.source_detail, s.sentiment, s.source FROM signals s
        WHERE s.collected_at >= ?
          AND s.source IN ('news', 'sec_filing')
          AND s.ticker != 'MACRO'
        ORDER BY
          CASE s.source WHEN 'sec_filing' THEN 1 ELSE 2 END,
          CASE s.sentiment WHEN 'very_bullish' THEN 1 WHEN 'very_bearish' THEN 2 ELSE 3 END,
          s.collected_at DESC LIMIT 20
    """, (_24h,))

    # Discovery — new stocks not in watchlist
    try:
        from collectors.discovery_collector import get_recent_discoveries
        discoveries = get_recent_discoveries(hours=24)
    except Exception:
        discoveries = []

    # Personal watchlist from env var (comma-separated tickers)
    watchlist_env = os.getenv("WATCHLIST", "")
    watchlist_tickers = [t.strip().upper() for t in watchlist_env.split(",") if t.strip()] if watchlist_env else []

    high_conviction = [s for s in scores if s["conviction_score"] >= 8.0]
    top_picks       = scores[:8]

    # ── HEADER ──────────────────────────────────────────────────────────────
    if avg_score >= 7:
        mood_icon, mood_text = "🟢", "BULLISH"
    elif avg_score >= 5.5:
        mood_icon, mood_text = "🟡", "MIXED"
    else:
        mood_icon, mood_text = "🔴", "CAUTIOUS"

    lines = [
        f"<b>📊 StockPulse  |  {date_str}  |  {time_str}</b>",
        f"━━━━━━━━━━━━━━━━━━━━━━━━━━",
        f"{mood_icon} Market: <b>{mood_text}</b>  |  Avg signal: {avg_score:.1f}/10 across {len(scores)} stocks",
        "",
    ]

    # ── SECTION 1: GLOBAL MACRO ──────────────────────────────────────────────
    macro_events = []
    seen_macro = set()
    for m in macro_signals:
        content = m.get("content", "")
        # Strip sentiment tag [BULLISH] etc and source attribution after |
        headline = re.sub(r'^\[.*?\]\s*', '', content.split("|")[0]).strip()
        headline = _clean(headline, 95)
        if headline and headline not in seen_macro:
            seen_macro.add(headline)
            sentiment = content.upper()
            arrow = "▲" if "BULLISH" in sentiment else ("▼" if "BEARISH" in sentiment else "→")
            macro_events.append((arrow, headline))

    if macro_events:
        lines.append("🌍 <b>GLOBAL MACRO</b>")
        for arrow, headline in macro_events[:5]:
            lines.append(f"  {arrow} {headline}")
        lines.append("")

    # ── SECTION 2: WHAT CEOs ARE SAYING ─────────────────────────────────────
    if executive_signals:
        lines.append("🎙 <b>WHAT CEOs ARE SAYING</b>")
        seen_exec = set()
        for e in executive_signals:
            source = _clean(e.get("source_detail", ""))
            content = e.get("content", "")
            # Extract the statement part after "exec_name: "
            stmt = content.split(":", 1)[1].strip() if ":" in content else content
            stmt = _clean(stmt, 110)
            key = source[:30]
            if key in seen_exec or not stmt:
                continue
            seen_exec.add(key)
            tkr   = e["ticker"]
            arrow = "▲" if "bullish" in e.get("sentiment", "") else ("▼" if "bearish" in e.get("sentiment", "") else "→")
            lines.append(f"  {arrow} <b>{source}</b>  {stmt}")
        lines.append("")

    # ── SECTION 3: CORPORATE NEWS & FILINGS ─────────────────────────────────
    sec_items   = []
    news_items  = []
    seen_corp   = set()
    seen_corp_t = set()

    for n in corporate_news:
        raw   = n.get("content", "")
        src   = n.get("source", "")
        tkr   = n.get("ticker", "")
        # Clean: strip "Title - Source Source" duplication at end
        title = re.sub(r'\s*[-–]\s*[\w\s\.]{3,40}$', '', raw).strip()
        title = _clean(title, 100)
        if not title or title in seen_corp:
            continue
        seen_corp.add(title)
        if src == "sec_filing":
            sec_items.append((tkr, title))
        elif tkr not in seen_corp_t:
            seen_corp_t.add(tkr)
            news_items.append((tkr, title, n.get("sentiment", "")))

    if sec_items or news_items:
        lines.append("🏢 <b>CORPORATE & FILINGS</b>")
        for tkr, title in sec_items[:3]:
            lines.append(f"  📋 <b>{tkr}</b>  {title}")
        for tkr, title, sent in news_items[:4]:
            arrow = "▲" if "bullish" in sent else ("▼" if "bearish" in sent else "→")
            lines.append(f"  {arrow} <b>{tkr}</b>  {title}")
        lines.append("")

    # ── SECTION 3: SECTORS AFFECTED ─────────────────────────────────────────
    seen_themes = set()
    sector_lines = []
    for c in cascades:
        theme = c.get("cascade_theme", "")
        if theme in seen_themes:
            continue
        seen_themes.add(theme)
        try:
            affected = json.loads(c["affected_tickers"]) if isinstance(c["affected_tickers"], str) else c["affected_tickers"]
            # Determine direction from reasoning
            reasoning = c.get("reasoning", "").upper()
            icon = "🟢" if "BULLISH" in reasoning else ("🔴" if "BEARISH" in reasoning else "🟡")
            sector_lines.append(f"  {icon} {_clean(theme)} → {', '.join(affected[:4])}")
        except Exception:
            pass

    if sector_lines:
        lines.append("📡 <b>SECTORS AFFECTED</b>")
        for sl in sector_lines[:5]:
            lines.append(sl)
        lines.append("")

    # ── SECTION 4: HIGH CONVICTION PICKS ────────────────────────────────────
    lines.append("🔥 <b>HIGH CONVICTION (8.0+)</b>")
    if high_conviction:
        for s in high_conviction:
            p = _get_price_data(s["ticker"])
            price_str = f"  <b>${p['price']:.2f}</b> {'▲' if p['pct_change'] >= 0 else '▼'}{abs(p['pct_change']):.1f}%" if p else ""
            risk_emoji = {"Low": "🔵", "Medium": "🟡", "High": "🟠", "Speculative": "🔴"}.get(s["risk_tier"], "⚪")
            lines.append(f"  {risk_emoji} <b>{s['ticker']}</b>  {s['conviction_score']:.1f}/10{price_str}")
            if s.get("reasoning"):
                sentences = [x.strip() for x in _clean(s["reasoning"]).split(".") if x.strip()]
                lines.append(f"     {'. '.join(sentences[:2])}.")
    else:
        lines.append("  No picks above 8.0 today — market conviction low")
    lines.append("")

    # ── SECTION 5: FULL WATCHLIST (scored stocks with 24h price) ────────────
    lines.append("📊 <b>WATCHLIST</b>")
    for s in top_picks:
        p          = _get_price_data(s["ticker"])
        bar        = "▲" if s["conviction_score"] >= 7 else ("→" if s["conviction_score"] >= 5 else "▼")
        price_str  = f"  <i>${p['price']:.2f}  {'▲' if p['pct_change'] >= 0 else '▼'}{abs(p['pct_change']):.1f}%</i>" if p else ""
        lines.append(f"  {bar} <b>{s['ticker']}</b>  {s['conviction_score']:.1f}/10{price_str}")

    # Personal watchlist tickers not already in top picks
    top_tickers = {s["ticker"] for s in top_picks}
    extra = [t for t in watchlist_tickers if t not in top_tickers]
    if extra:
        lines.append("  <i>── your watchlist ──</i>")
        for tkr in extra:
            p = _get_price_data(tkr)
            if p:
                arrow = "▲" if p["pct_change"] >= 0 else "▼"
                lines.append(f"  {arrow} <b>{tkr}</b>  <i>${p['price']:.2f}  {arrow}{abs(p['pct_change']):.1f}%</i>")
            else:
                lines.append(f"  → <b>{tkr}</b>  no price data")
    lines.append("")

    # ── SECTION 6: STOCKS TO WATCH (discovery) ──────────────────────────────
    if discoveries:
        lines.append("🔍 <b>STOCKS TO WATCH</b>  <i>(not in your list)</i>")
        type_labels = {
            "social_trending":   "📣 Trending",
            "analyst_upgrade":   "⬆️ Analyst Upgrade",
            "options_activity":  "🎯 Unusual Options",
            "short_squeeze":     "💥 Short Squeeze",
            "earnings_upcoming": "📅 Earnings Due",
            "small_cap_momentum":"🚀 Small Cap Move",
            "ipo_watch":         "🆕 IPO/New Listing",
            "news_mention":      "📰 In the News",
        }
        seen_disc = set()
        for d in discoveries[:6]:
            tkr = d["ticker"]
            if tkr in seen_disc:
                continue
            seen_disc.add(tkr)
            label  = type_labels.get(d["signal_type"], "👀 Watch")
            reason = _clean(d["reason"], 90)
            p      = _get_price_data(tkr)
            price_str = f"  <i>${p['price']:.2f} {'▲' if p['pct_change'] >= 0 else '▼'}{abs(p['pct_change']):.1f}%</i>" if p else ""
            lines.append(f"  {label}  <b>{tkr}</b>{price_str}")
            lines.append(f"     {reason}")
        lines.append("")

    # ── SECTION 7: VIEW ─────────────────────────────────────────────────────
    lines.append("💡 <b>VIEW</b>")
    for line in _build_opinion(scores, macro_signals, cascades, avg_score):
        lines.append(f"  {_clean(line)}")

    lines.append("")
    lines.append("━━━━━━━━━━━━━━━━━━━━━━━━━━")
    lines.append("<i>Next scan 30 min  |  msg <b>update</b> anytime  |  <b>alert NVDA</b> for specific stock</i>")

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
