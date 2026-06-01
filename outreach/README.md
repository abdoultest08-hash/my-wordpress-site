# Outreach Automation Pipeline

Automated outreach system: Excel leads → custom mock website → screenshot → Gmail draft.

## Folder Structure

```
outreach/
├── pipeline.py          # Main runner — start here
├── generate_site.py     # Claude API → custom HTML per business
├── screenshot.js        # Puppeteer → PNG screenshot of mock site
├── gmail_draft.py       # Gmail API → create draft with embedded screenshot
├── leads_example.xlsx   # Sample leads file — replace with your own
├── .env.example         # Environment variables template
├── sites/               # Generated HTML files (one per lead)
├── screenshots/         # PNG screenshots (one per lead)
└── outreach_log.json    # Run log — tracks what's been processed
```

## Setup (One-Time)

### 1. Environment variables
```bash
cp .env.example .env
# Edit .env with your values
export $(cat .env | xargs)
```

### 2. Gmail API credentials
1. Go to [console.cloud.google.com](https://console.cloud.google.com)
2. Create a new project → Enable **Gmail API**
3. Go to **Credentials** → Create **OAuth 2.0 Client ID** → Desktop App
4. Download JSON → save as `outreach/credentials.json`
5. First run will open a browser to authorize — then `token.json` is saved automatically

### 3. Your leads Excel
The Excel file needs these columns (row 1 = headers):
| Column | Required | Example |
|---|---|---|
| business_name | ✅ | Rodriguez Plumbing |
| owner_name | ✅ | Carlos |
| industry | ✅ | plumber |
| city | ✅ | Los Angeles |
| state | | CA |
| email | ✅ | carlos@example.com |
| phone | | (213) 555-0101 |
| notes | | No website found |

## Running

```bash
cd outreach

# Process 10 leads from your Excel → generate sites → screenshot → Gmail drafts
python3 pipeline.py --leads your_leads.xlsx --limit 10

# Test without Gmail (just generate sites + screenshots)
python3 pipeline.py --leads your_leads.xlsx --limit 3 --no-gmail

# Process a different number
python3 pipeline.py --leads your_leads.xlsx --limit 5
```

## What Happens

1. **Per lead**: Claude API generates a full custom HTML website (tailored to their industry, city, business name, phone)
2. **Screenshot**: Puppeteer renders it headless and captures 1280×800 above-the-fold PNG
3. **Gmail Draft**: Creates a personalized email with the screenshot embedded — lands in your **Drafts** folder
4. **Log**: `outreach_log.json` tracks every lead so re-runs skip already-processed ones

## Review & Send

Go to **Gmail → Drafts** — each email has:
- Personalized subject: `I built a free website mock for [Business Name] 🏠`
- The screenshot embedded in the body
- Your signature at the bottom

Review, tweak the copy if needed, and hit Send.

## Tips

- Use LinkedIn Sales Navigator, Google Maps, or Yelp to find leads with no website
- Industries that convert well: plumbers, landscapers, cleaners, painters, electricians
- Keep daily volume at 10–20 to avoid Gmail sending limits
- Personalize the subject line per niche for better open rates
