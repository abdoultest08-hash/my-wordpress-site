"""
Email notifications — daily 7am digest and instant high-conviction alerts.

Uses SendGrid (free tier: 100 emails/day).
Sign up at sendgrid.com, get an API key, add to .env as SENDGRID_API_KEY.
Also set a verified sender email as EMAIL_FROM.
"""

import json
import os
from datetime import datetime, timezone
from pathlib import Path

import sys
sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import execute, insert

# ---------------------------------------------------------------------------
# SendGrid client (lazy import — only needed when actually sending)
# ---------------------------------------------------------------------------

def _get_sg():
    try:
        import sendgrid
        from sendgrid.helpers.mail import Mail
        api_key = os.getenv("SENDGRID_API_KEY", "")
        if not api_key:
            raise ValueError("SENDGRID_API_KEY not set")
        return sendgrid.SendGridAPIClient(api_key=api_key), Mail
    except ImportError:
        raise ImportError("Run: pip install sendgrid")


# ---------------------------------------------------------------------------
# HTML email builder — daily digest
# ---------------------------------------------------------------------------

def _build_digest_html(scores: list[dict], cascade_events: list[dict], date_str: str) -> str:
    alert_scores = [s for s in scores if s["conviction_score"] >= 8.0]
    watch_scores = [s for s in scores if 6.0 <= s["conviction_score"] < 8.0]
    low_scores   = [s for s in scores if s["conviction_score"] < 6.0]

    def risk_badge(tier):
        colors = {"Low": "#22c55e", "Medium": "#f59e0b", "High": "#ef4444", "Speculative": "#7c3aed"}
        return f'<span style="background:{colors.get(tier,"#6b7280")};color:white;padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600">{tier}</span>'

    def score_bar(score):
        pct = int(score * 10)
        color = "#22c55e" if score >= 8 else "#f59e0b" if score >= 6 else "#6b7280"
        return f'<div style="background:#e5e7eb;border-radius:4px;height:8px;width:120px;display:inline-block"><div style="background:{color};width:{pct}%;height:8px;border-radius:4px"></div></div>'

    def delta_str(s):
        prev = s.get("prev_day_score")
        if prev is None:
            return ""
        d = s["conviction_score"] - prev
        if abs(d) < 0.1:
            return '<span style="color:#6b7280">→</span>'
        arrow = "↑" if d > 0 else "↓"
        color = "#22c55e" if d > 0 else "#ef4444"
        return f'<span style="color:{color}">{arrow} {abs(d):.1f}</span>'

    def ticker_row(s):
        return f"""
        <tr style="border-bottom:1px solid #f3f4f6">
          <td style="padding:12px 8px;font-weight:700;font-size:16px">{s['ticker']}</td>
          <td style="padding:12px 8px">
            <span style="font-size:20px;font-weight:700">{s['conviction_score']:.1f}</span>
            <span style="color:#9ca3af;font-size:12px">/10</span>
            &nbsp;{delta_str(s)}
          </td>
          <td style="padding:12px 8px">{score_bar(s['conviction_score'])}</td>
          <td style="padding:12px 8px">{risk_badge(s['risk_tier'])}</td>
          <td style="padding:12px 8px;color:#6b7280;font-size:13px;max-width:300px">{s.get('reasoning','')[:120]}</td>
        </tr>"""

    # Cascade section
    cascade_html = ""
    if cascade_events:
        rows = ""
        for e in cascade_events[:5]:
            affected = json.loads(e.get("affected_tickers", "[]"))
            scores_map = json.loads(e.get("proximity_scores", "{}"))
            tickers_html = " ".join(
                f'<span style="background:#dbeafe;color:#1d4ed8;padding:2px 6px;border-radius:4px;font-size:12px;margin:2px">{t} {int(scores_map.get(t,0)*100)}%</span>'
                for t in affected
            )
            rows += f"""
            <tr style="border-bottom:1px solid #f3f4f6">
              <td style="padding:10px 8px;font-size:13px;color:#374151">{e.get('cascade_theme','')}</td>
              <td style="padding:10px 8px;font-size:12px;color:#6b7280;max-width:250px">{e.get('catalyst_headline','')[:100]}</td>
              <td style="padding:10px 8px">{tickers_html}</td>
            </tr>"""

        cascade_html = f"""
        <h2 style="color:#1d4ed8;margin-top:32px;margin-bottom:8px">🔗 Thematic Cascades</h2>
        <p style="color:#6b7280;font-size:13px;margin-bottom:12px">Indirect effects detected — one event lifting or weighing on related tickers</p>
        <table style="width:100%;border-collapse:collapse;background:#f8fafc;border-radius:8px">
          <tr style="background:#e0e7ff;font-size:12px;color:#4338ca">
            <th style="padding:8px;text-align:left">Theme</th>
            <th style="padding:8px;text-align:left">Catalyst</th>
            <th style="padding:8px;text-align:left">Affected Tickers</th>
          </tr>
          {rows}
        </table>"""

    # Alert section
    alert_html = ""
    if alert_scores:
        alert_rows = "".join(ticker_row(s) for s in alert_scores)
        alert_html = f"""
        <h2 style="color:#dc2626;margin-top:32px;margin-bottom:8px">⚡ High Conviction Alerts (Score ≥ 8)</h2>
        <table style="width:100%;border-collapse:collapse">
          <tr style="background:#fef2f2;font-size:12px;color:#991b1b">
            <th style="padding:8px;text-align:left">Ticker</th>
            <th style="padding:8px;text-align:left">Score</th>
            <th style="padding:8px;text-align:left">Strength</th>
            <th style="padding:8px;text-align:left">Risk</th>
            <th style="padding:8px;text-align:left">Reasoning</th>
          </tr>
          {alert_rows}
        </table>"""

    # Watch section
    watch_rows = "".join(ticker_row(s) for s in watch_scores)
    watch_html = ""
    if watch_scores:
        watch_html = f"""
        <h2 style="color:#d97706;margin-top:32px;margin-bottom:8px">👀 On Watch (Score 6–8)</h2>
        <table style="width:100%;border-collapse:collapse">
          <tr style="background:#fffbeb;font-size:12px;color:#92400e">
            <th style="padding:8px;text-align:left">Ticker</th>
            <th style="padding:8px;text-align:left">Score</th>
            <th style="padding:8px;text-align:left">Strength</th>
            <th style="padding:8px;text-align:left">Risk</th>
            <th style="padding:8px;text-align:left">Reasoning</th>
          </tr>
          {watch_rows}
        </table>"""

    # Low / quiet section (collapsed summary)
    low_summary = ", ".join(f"{s['ticker']} ({s['conviction_score']:.1f})" for s in low_scores)
    low_html = f'<p style="color:#9ca3af;font-size:13px;margin-top:24px">💤 Quiet today: {low_summary}</p>' if low_scores else ""

    return f"""
<!DOCTYPE html>
<html>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;max-width:800px;margin:0 auto;padding:24px;background:#ffffff;color:#111827">

  <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:24px;border-radius:12px;margin-bottom:24px">
    <h1 style="color:white;margin:0;font-size:24px">📈 StockPulse Daily Digest</h1>
    <p style="color:#93c5fd;margin:4px 0 0">{date_str} &nbsp;·&nbsp; {len(scores)} stocks scored &nbsp;·&nbsp; {len(alert_scores)} alerts</p>
  </div>

  {alert_html}
  {watch_html}
  {cascade_html}
  {low_html}

  <hr style="border:none;border-top:1px solid #e5e7eb;margin:32px 0">
  <p style="color:#9ca3af;font-size:12px;text-align:center">
    StockPulse · Automated signal scoring · Not financial advice<br>
    Signals from Reddit, news feeds, and thematic cascade mapping
  </p>

</body>
</html>"""


# ---------------------------------------------------------------------------
# HTML email builder — instant alert
# ---------------------------------------------------------------------------

def _build_alert_html(ticker: str, score: float, risk_tier: str, reasoning: str) -> str:
    color = "#dc2626" if score >= 9 else "#d97706"
    return f"""
<!DOCTYPE html>
<html>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;max-width:600px;margin:0 auto;padding:24px">
  <div style="background:{color};padding:20px;border-radius:12px;text-align:center;margin-bottom:24px">
    <h1 style="color:white;margin:0;font-size:28px">⚡ High Conviction Alert</h1>
    <p style="color:rgba(255,255,255,0.85);margin:4px 0 0">StockPulse just detected a strong signal</p>
  </div>
  <div style="border:2px solid {color};border-radius:12px;padding:24px;text-align:center;margin-bottom:24px">
    <div style="font-size:48px;font-weight:800;color:#111827">{ticker}</div>
    <div style="font-size:36px;font-weight:700;color:{color}">{score:.1f}<span style="font-size:18px;color:#6b7280">/10</span></div>
    <div style="background:#f3f4f6;display:inline-block;padding:4px 16px;border-radius:9999px;font-size:14px;margin-top:8px">
      Risk: <strong>{risk_tier}</strong>
    </div>
  </div>
  <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-bottom:24px">
    <p style="color:#374151;margin:0;font-size:14px;line-height:1.6"><strong>Why:</strong> {reasoning}</p>
  </div>
  <p style="color:#9ca3af;font-size:11px;text-align:center">
    Not financial advice · StockPulse automated alert
  </p>
</body>
</html>"""


# ---------------------------------------------------------------------------
# Send functions
# ---------------------------------------------------------------------------

def send_daily_digest() -> bool:
    """
    Build and send the 7am daily digest email.
    Pulls today's scores from the database.
    Returns True on success.
    """
    to_email   = os.getenv("EMAIL_TO", "")
    from_email = os.getenv("EMAIL_FROM", "")
    if not to_email or not from_email:
        print("[Email] EMAIL_TO or EMAIL_FROM not set — skipping")
        return False

    scores = execute("""
        SELECT ds.*, t.name, t.theme, t.market_cap_tier
        FROM daily_scores ds
        JOIN tickers t ON t.symbol = ds.ticker
        WHERE ds.date = date('now')
        ORDER BY ds.conviction_score DESC
    """)

    if not scores:
        print("[Email] No scores for today — skipping digest")
        return False

    cascades = execute("""
        SELECT * FROM cascade_events
        WHERE date(detected_at) = date('now')
        ORDER BY detected_at DESC
        LIMIT 10
    """)

    date_str = datetime.now(timezone.utc).strftime("%A, %B %-d %Y")
    subject  = f"StockPulse Digest — {datetime.now().strftime('%b %-d')} · {len([s for s in scores if s['conviction_score'] >= 8])} alerts"
    html     = _build_digest_html(scores, cascades, date_str)

    return _send_email(to_email, from_email, subject, html, alert_type="daily_digest", tickers=[s["ticker"] for s in scores])


def send_instant_alert(ticker: str, score: float, risk_tier: str, reasoning: str) -> bool:
    """
    Send an instant alert email when conviction score hits the threshold.
    Returns True on success.
    """
    to_email   = os.getenv("EMAIL_TO", "")
    from_email = os.getenv("EMAIL_FROM", "")
    if not to_email or not from_email:
        print("[Email] EMAIL_TO or EMAIL_FROM not set — skipping")
        return False

    subject = f"⚡ StockPulse Alert: {ticker} scored {score:.1f}/10 [{risk_tier} Risk]"
    html    = _build_alert_html(ticker, score, risk_tier, reasoning)

    return _send_email(to_email, from_email, subject, html, alert_type="instant", tickers=[ticker],
                       score=score, risk_tier=risk_tier, reasoning=reasoning)


def _send_email(to_email, from_email, subject, html,
                alert_type, tickers, score=None, risk_tier=None, reasoning=None) -> bool:
    try:
        sg, Mail = _get_sg()
        message = Mail(
            from_email=from_email,
            to_emails=to_email,
            subject=subject,
            html_content=html,
        )
        response = sg.send(message)
        success = response.status_code in (200, 202)

        # Log to alerts table
        for ticker in tickers:
            try:
                insert("alerts", {
                    "ticker":           ticker,
                    "alert_type":       alert_type,
                    "conviction_score": score or 0,
                    "risk_tier":        risk_tier or "Medium",
                    "trigger_reason":   reasoning or f"{alert_type} digest",
                    "channels_sent":    json.dumps(["email"]),
                    "email_sent":       1 if success else 0,
                    "sms_sent":         0,
                    "sent_at":          datetime.now(timezone.utc).isoformat(),
                })
            except Exception:
                pass

        status = "✅ sent" if success else f"❌ failed ({response.status_code})"
        print(f"[Email] {alert_type} → {to_email} {status}")
        return success

    except Exception as e:
        print(f"[Email] Error: {e}")
        return False


# ---------------------------------------------------------------------------
# Preview — generate HTML without sending (for testing)
# ---------------------------------------------------------------------------

def preview_digest(output_path: str = "/tmp/stockpulse_digest_preview.html"):
    """Write the digest HTML to a file for visual inspection."""
    scores = execute("""
        SELECT ds.*, t.name, t.theme, t.market_cap_tier
        FROM daily_scores ds
        JOIN tickers t ON t.symbol = ds.ticker
        WHERE ds.date = date('now')
        ORDER BY ds.conviction_score DESC
    """)
    cascades = execute("SELECT * FROM cascade_events ORDER BY detected_at DESC LIMIT 10")
    date_str = datetime.now().strftime("%A, %B %-d %Y")
    html = _build_digest_html(scores, cascades, date_str)
    Path(output_path).write_text(html)
    print(f"[Email] Preview saved to {output_path}")
    return output_path


if __name__ == "__main__":
    path = preview_digest()
    print(f"Open in browser: file://{path}")
