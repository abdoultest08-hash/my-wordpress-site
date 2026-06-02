# Railway Environment Variables

Paste each of these into Railway:
**Dashboard → your service → Variables → Add variable**

## Required — add all 4 Twilio values

| Variable | Where to find it |
|---|---|
| `TWILIO_ACCOUNT_SID` | console.twilio.com → Account SID |
| `TWILIO_AUTH_TOKEN` | console.twilio.com → Auth Token |
| `TWILIO_FROM_NUMBER` | Your Twilio phone number e.g. +447576551637 |
| `SMS_TO_NUMBER` | Your personal mobile e.g. +447440113037 |

## Auto-injected by Railway — do NOT set manually

| Variable | Notes |
|---|---|
| `DATABASE_URL` | Set automatically when you add the PostgreSQL plugin |
| `PORT` | Set automatically |

## Optional — improves signal coverage

| Variable | Where to get it |
|---|---|
| `NEWS_API_KEY` | newsapi.org → free account |
| `REDDIT_CLIENT_ID` | reddit.com/prefs/apps |
| `REDDIT_CLIENT_SECRET` | reddit.com/prefs/apps |

## Timing (optional — defaults shown)

| Variable | Default |
|---|---|
| `DIGEST_TIME` | `07:00` |
| `SCAN_INTERVAL_MINUTES` | `30` |
| `CONVICTION_ALERT_THRESHOLD` | `8.0` |

## SMS Reply-to-Trigger (Twilio Webhook)

After deploying, you can text your Twilio number to get an on-demand update:

**Commands you can text:**
- `summary` or `update` → sends the full 3-part daily summary right now
- `alert NVDA` → instant analysis + alert for any ticker
- `status` → confirms the system is running
- `top` → top picks today
- `help` → shows all commands

**Setup in Twilio Console (one-time):**
1. Go to console.twilio.com → Phone Numbers → your number
2. Under "Messaging" → "A message comes in":
   - Webhook: `https://YOUR-RAILWAY-URL.railway.app/sms`
   - Method: HTTP POST
3. Save

Your Railway URL is shown in Railway Dashboard → your service → Settings → Domains.

## One-time triggers (delete after use)

| Variable | Effect |
|---|---|
| `FORCE_DAILY_SUMMARY=true` | Sends the daily summary immediately on startup |
| `TEST_TICKER=NVDA` | Fires an instant alert for NVDA on startup |
