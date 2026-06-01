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
