# Railway Environment Variables

Paste each of these into your Railway project:
**Railway dashboard → your project → Variables → Add variable**

## Required — app won't start without these

| Variable | Where to get it |
|---|---|
| `SENDGRID_API_KEY` | sendgrid.com → Settings → API Keys |
| `EMAIL_FROM` | A SendGrid verified sender address (e.g. alerts@yourdomain.com) |
| `EMAIL_TO` | abdoultest08@gmail.com |
| `TWILIO_ACCOUNT_SID` | twilio.com → Console → Account SID |
| `TWILIO_AUTH_TOKEN` | twilio.com → Console → Auth Token |
| `TWILIO_FROM_NUMBER` | Your Twilio phone number e.g. +12025551234 |
| `SMS_TO_NUMBER` | Your mobile number e.g. +12025559876 |

## Auto-injected by Railway — do NOT set manually

| Variable | Notes |
|---|---|
| `DATABASE_URL` | Railway sets this automatically when you add a PostgreSQL plugin |
| `PORT` | Railway sets this automatically |

## Optional — improves signal coverage

| Variable | Where to get it |
|---|---|
| `NEWS_API_KEY` | newsapi.org → free account → API key |
| `REDDIT_CLIENT_ID` | reddit.com/prefs/apps |
| `REDDIT_CLIENT_SECRET` | reddit.com/prefs/apps |
| `OPENAI_API_KEY` | platform.openai.com → API keys |

## Timing settings (optional overrides)

| Variable | Default | Notes |
|---|---|---|
| `DIGEST_TIME` | `07:00` | Time for daily email (24h format) |
| `DIGEST_TIMEZONE` | `America/New_York` | Your timezone |
| `SCAN_INTERVAL_MINUTES` | `30` | How often to scan for signals |
| `CONVICTION_ALERT_THRESHOLD` | `8.0` | Score that triggers instant alert |
