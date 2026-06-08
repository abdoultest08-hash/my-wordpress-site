# Outreach Automation System — Project Summary

**Last updated:** 8 June 2026
**Built for:** Abdoul Sandwidi (abdoultest08@gmail.com)
**Git branch:** `claude/sweet-hopper-k0L92`

---

## What This Project Does

You find local service businesses (plumbers, roofers, cleaners, etc.) that have no website or a poor one. The system automatically:

1. Reads a list of those businesses from an Excel file
2. Uses AI (Claude) to build a professional-looking mock website for each business, personalised with their real name, phone number, and logo
3. Takes a screenshot of that website so it looks like a finished product
4. Sends a casual, personal email to the business owner with the screenshot attached — as if you stumbled across their business and made them a free preview
5. Tracks everything in a CRM database so you can see who replied, who booked a meeting, and who became a paying client

The goal is to turn a cold lead into a warm conversation: "Here's what your website could look like — worth a chat?"

---

## Files and Folders

```
outreach/
  pipeline.py           — The master script. Run this to kick everything off.
  generate_site.py      — Builds the mock HTML website using AI
  screenshot.js         — Takes a screenshot of the website (Node.js)
  gmail_draft.py        — Sends or drafts emails via Gmail
  accounts.py           — Manages your two Gmail accounts + warm-up rules
  supabase_client.py    — Saves data to the CRM database
  hero_images.py        — Generates background images for mock sites
  hero_images/          — Saved background image files (auto-generated)
  sites/                — Generated HTML files (one per business)
  screenshots/          — Screenshot images (one per business)
  find_leads.py         — Finds leads automatically via Google Maps
  schema.sql            — CRM database setup (run once in Supabase)
  schema_v2_migration.sql — Database update for A/B testing columns
  leads_ready.xlsx      — 847 leads with emails, ready to send
  leads_no_email.xlsx   — 701 leads with no website but no email yet
  leads_template.xlsx   — Blank template for manual lead entry
  .env                  — Your secret keys (NOT saved to Git)

crm/
  src/pages/Leads.jsx   — The Leads page of your web CRM
  (+ other React pages for Dashboard, Pipeline, KPIs, etc.)
```

---

## The Pipeline Step by Step

Here is exactly what happens when you run the pipeline for a lead:

### Step 1 — Read the lead
The script opens your Excel file and reads each row: business name, email, phone, industry, city, logo URL, etc.

### Step 2 — Save to CRM
The lead is saved to your Supabase database as "pending". If the lead already exists (matched by email address) and was already emailed, it is skipped automatically.

### Step 3 — Generate the mock website
The AI (Claude claude-sonnet-4-6) writes a complete HTML webpage for the business. The layout is always:
- A **navbar** with their logo top-left, navigation links in the centre, and their phone number + a "Get a Quote" button top-right
- A **hero section** with their city/trade headline on the left and a lead-capture form card on the right, over a background image
- A **services strip** below with 3 relevant services listed

The website uses a navy and blue colour scheme (`#0D2137` / `#1A56DB`). Everything is embedded directly into the HTML file so it works offline — including the background image (generated from Pillow, a Python image library) and the business logo (downloaded and embedded as base64).

The file is saved to `outreach/sites/businessname.html`.

### Step 4 — Screenshot
A Node.js script (Puppeteer) opens the HTML file in a headless browser at 1440x900 pixels (a real laptop resolution), waits for images to load, and saves a screenshot to `outreach/screenshots/businessname.png`.

### Step 5 — Send the email
The email is sent from one of your two Gmail accounts, chosen based on which one has the most sends remaining today. The email:
- **Subject:** "Question for [Business Name]"
- **Body:** A short, casual paragraph (no bold text, no orange buttons, no "Hi [First Name]"), followed by the screenshot embedded as an image, a line about local businesses getting 5+ extra quote requests per month from Google, and your name
- The exact wording depends on whether the business has no website (`new` pitch) or a bad one (`upgrade` pitch)

### Step 6 — Update CRM
The lead status is updated to "email_sent" in the database. The sending account's daily count is incremented.

### Step 7 — Wait before next email
A random delay is calculated based on how many emails are left to send today. Sends are spread across 9am–5pm (Pacific time), Monday to Friday, with ±30% randomness so the pattern doesn't look robotic.

---

## How to Run a Batch

Open a terminal, go to the outreach folder, load your environment variables, and run:

```bash
cd outreach
export $(cat .env | xargs)

# Test with 1 lead in draft mode first (saves to Gmail Drafts, does not send):
python3 pipeline.py --leads leads_ready.xlsx --limit 1 --draft

# Send for real to 10 leads:
python3 pipeline.py --leads leads_ready.xlsx --limit 10

# Generate mock sites and screenshots only, no emails:
python3 pipeline.py --leads leads_ready.xlsx --limit 10 --no-send
```

After running, check your CRM at: https://brilliant-gnome-d72d80.netlify.app

---

## Your Gmail Accounts

| Account | Purpose | Start Date |
|---|---|---|
| sitesbyabs@gmail.com | Primary sending account | 3 June 2026 |
| pagesforlocals@gmail.com | Secondary sending account | 3 June 2026 |

**Warm-up schedule** (daily limits per account, based on account age):

| Account Age | Max Emails/Day |
|---|---|
| Days 0–4 | 10 |
| Days 5–9 | 15 |
| Days 10–19 | 20 |
| Days 20–39 | 30 |
| Day 40+ | 40 |

Both accounts combined means your daily capacity grows from 20/day to 80/day over time.

The system automatically picks whichever account has more remaining quota and balances the load between them.

---

## The Email Copy

Two versions of the email body exist, selected automatically based on the `pitch_type` column in your Excel file:

**For businesses with NO website** (`pitch_type = new`):
> "Heard about [Business] from someone the other day. They said your work is solid but you don't have a website yet, which means you're probably missing out on quote requests every month."

**For businesses with a BAD website** (`pitch_type = upgrade`):
> "Heard about [Business] from someone the other day. They said your work is solid but the website doesn't really reflect that."

Both end with:
> "I put together a quick mock-up of what [one / an updated version] could look like for you."
> [Screenshot image]
> "Most [industry]s in your area pick up 5+ extra quote requests a month just from Google."
> "Worth a look? Would love to know what you think."
> Abdoul Sandwidi

The `copy_version` field (v1, v2, etc.) lets you A/B test different email copy in the future. Change the `COPY_VERSION` environment variable or set it in `.env` to switch versions.

---

## Your Lead Files

| File | Leads | Status |
|---|---|---|
| `leads_ready.xlsx` | 847 | Have emails + logos. `pitch_type = upgrade` (had websites). Ready to send. |
| `leads_no_email.xlsx` | 701 | No website found, but no email address either. Need manual email hunting. |
| `leads_template.xlsx` | 0 | Blank template with column headers. Use for manual entries. |

**To find more leads with no website**, use the lead finder tool (requires a Google Maps API key):

```bash
python3 find_leads.py --industry "plumber" --city "Manchester" --limit 20
```

This searches Google Maps for businesses matching the industry and city, filters out any that already have a website, and saves the results to a new Excel file. You then need to find email addresses manually (Facebook, Checkatrade, Yell.com) and add them to the file before running the pipeline.

---

## The CRM Database (Supabase)

The database lives on Supabase (project ID: `oxvzfmfqfkddxqniyykq`) and has four tables:

| Table | What it stores |
|---|---|
| `leads` | Every business: name, email, status, when emailed, when replied, deal value, etc. |
| `email_log` | Every email sent: which account, subject, Gmail message ID, copy version |
| `send_counts` | How many emails each account has sent per day (for warm-up tracking) |
| `replies` | Incoming replies: the Gmail thread ID, a snippet of the message, whether it's a positive reply |

**Lead statuses** (the pipeline moves leads through these stages):
`pending` → `site_generated` → `email_sent` → `replied` → `positive` → `meeting_booked` → `closed` / `not_interested`

### Current Issue: IP Allowlist Block

The cloud server's IP address is blocked by Supabase. When this happens, all database operations are saved locally to `outreach/local_log.json` instead, and you can push them to the real database later.

**To fix:** Go to your Supabase project → Settings → Network → remove or disable IP restrictions.

**To sync after fixing:**
```bash
cd outreach
export $(cat .env | xargs)
python3 supabase_client.py --sync
```

### Schema v2 Migration

You need to run a database migration to add the `copy_version` and `pitch_type` columns. Go to your Supabase project → SQL Editor → paste and run the contents of `outreach/schema_v2_migration.sql`.

---

## The Web CRM App

The CRM is a React web app you can use to manage leads visually.

**Live URL:** https://brilliant-gnome-d72d80.netlify.app

**Pages:**
- **Dashboard** — KPI summary cards (total leads, emails sent, replies, meetings, revenue)
- **Pipeline** — Kanban board showing leads by stage (drag and drop)
- **Leads** — Full table of all leads with search, status filter, and Excel import/export
- **Lead Detail** — Click any lead to see all details and update the status manually
- **KPIs** — Charts showing performance over time

**The Leads page can:**
- Import an Excel file directly into the database (no need to run pipeline.py for CRM-only imports)
- Export all leads to a fresh Excel file with all current data
- Search by business name, owner, city, or email
- Filter by status (pending, email sent, replied, etc.)

---

## Background Images for Mock Sites

The `hero_images.py` script generates gradient background images for each industry using Python (no internet required). These are placeholder images — coloured gradients that give each industry a distinct look.

**Industries covered:** plumber, electrician, roofer, landscaper/lawn/tree, cleaning/maid, HVAC/heating/air conditioning, pest control, painter, auto detailing, flooring, handyman/remodel/construction.

These gradient images work, but real photos will look far more professional. The plan is to replace them with real niche photos from Unsplash or Pexels (see Outstanding Issues below).

---

## Credentials and Secret Files

These files exist on your machine but are **not saved to Git** (they are in `.gitignore`):

| File | What it is |
|---|---|
| `outreach/.env` | All your API keys (Anthropic, Supabase, Google Maps) |
| `outreach/token.json` | Gmail OAuth token for sitesbyabs@gmail.com |
| `outreach/token_pagesforlocals.json` | Gmail OAuth token for pagesforlocals@gmail.com |
| `outreach/credentials.json` | Google Cloud OAuth2 app credentials |

**Your .env file should contain:**

```
ANTHROPIC_API_KEY=...
YOUR_NAME=Abdoul Sandwidi
YOUR_EMAIL=sitesbyabs@gmail.com
YOUR_WEBSITE=sitesbyabs.com
SUPABASE_URL=https://oxvzfmfqfkddxqniyykq.supabase.co
SUPABASE_KEY=...
SUPABASE_SERVICE_KEY=...
GOOGLE_MAPS_KEY=...   (optional, for find_leads.py)
COPY_VERSION=v1       (optional, default is v1)
```

---

## Outstanding Issues and Next Steps

These are the things still to be done, in rough order of priority:

### 1. Hero background images not rendering in screenshots
The background images exist and are embedded into the HTML as base64, but they are not visually appearing in screenshots. The short-term fix is to replace the gradient placeholder images with real photos (JPEG files from Unsplash or Pexels). Download one photo per industry, save it into `outreach/hero_images/` with the matching filename (e.g. `plumber.jpg`, `roofer.jpg`), and the pipeline will pick them up automatically.

### 2. Fix Supabase IP allowlist
See the instructions in the CRM Database section above. Until this is fixed, all pipeline data is queued in `local_log.json` and not visible in the CRM.

### 3. Run schema v2 migration
Open Supabase → SQL Editor → run `schema_v2_migration.sql` to add the `copy_version` and `pitch_type` columns. The pipeline will error without them if Supabase is reachable.

### 4. First real send
The pipeline has only been tested in `--draft` mode so far. Once the mock-up quality looks good, run a real send to a small batch (5–10 leads) to verify everything works end to end.

### 5. Build a reply detector
Not yet built. This would be a script that polls your Gmail inboxes, detects replies to your outreach emails, and automatically updates the lead status in the CRM to "replied" or "positive". The `log_reply()` function in `supabase_client.py` is ready and waiting to be called.

### 6. Telegram notifications
Not yet built. The plan is a Telegram bot that pings you when someone replies and sends a daily summary (X emails sent, X replies today, X positive leads). Easy to add once the reply detector is working.

### 7. Set up a VPS for automated daily runs
Not yet set up. A DigitalOcean Droplet ($5/month) running Ubuntu would let you schedule the pipeline as a daily cron job so it runs automatically at 9am without you needing to do anything. The pipeline is fully automated once running.

### 8. Connect GitHub to Netlify for auto-deploy
Currently the CRM is deployed manually (you have to build the `dist` folder and drag it to Netlify). Connecting the GitHub repo to Netlify would make it update automatically whenever you push code changes.

### 9. Open rate tracking
Not yet implemented. This would require a small server-side endpoint that serves a 1x1 transparent pixel image, and embedding that image in outgoing emails. When the recipient opens the email, their email client loads the pixel and you log the open.

### 10. Find emails for the 701 no-email leads
`leads_no_email.xlsx` has 701 businesses with no website — highly motivated prospects — but no email address found. To use these, manually search for each business on Facebook, Checkatrade, Yell.com, or their Google Maps listing to find a contact email, add it to the spreadsheet, and set `pitch_type = new`.

---

## Quick Reference Commands

```bash
# Load environment variables (do this before any command)
cd outreach && export $(cat .env | xargs)

# Run pipeline — draft mode (safe test, saves to Gmail Drafts)
python3 pipeline.py --leads leads_ready.xlsx --limit 1 --draft

# Run pipeline — real send
python3 pipeline.py --leads leads_ready.xlsx --limit 10

# Generate mock sites only (no screenshots, no emails)
python3 pipeline.py --leads leads_ready.xlsx --limit 10 --no-send

# Find new leads (requires GOOGLE_MAPS_KEY)
python3 find_leads.py --industry "roofer" --city "Birmingham" --limit 20

# Sync local log to Supabase (after fixing IP allowlist)
python3 supabase_client.py --sync

# Check account status and daily limits
python3 accounts.py

# Generate a test mock site (saves to test_output.html)
python3 generate_site.py

# Pre-generate hero images for all industries
python3 hero_images.py
```
