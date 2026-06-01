# StockPulse

An automated stock signal and scoring system that monitors Reddit, news feeds, and CEO social signals — then scores stocks by conviction and alerts you when something interesting happens.

## Project Structure

```
stockpulse/
├── investor_profile.json     ← YOUR preferences, risk tolerance, sectors, alert settings
│
├── collectors/               ← Data gathering (the "eyes and ears")
│   ├── reddit_collector.py   ← Scrapes Reddit communities for stock mentions & sentiment
│   ├── news_collector.py     ← Pulls RSS/news feeds (Reuters, CNBC, TechCrunch, etc.)
│   └── ceo_signals.py        ← Monitors CEO tweets/posts for market-moving statements
│
├── scoring/                  ← The brain
│   ├── signal_scorer.py      ← Converts raw mentions into a 1–10 conviction score
│   ├── risk_classifier.py    ← Assigns Low/Medium/High/Speculative risk tier
│   └── thematic_cascade.py   ← Maps indirect effects (e.g. SpaceX IPO → satellite parts)
│
├── notifications/            ← Alert delivery
│   ├── email_sender.py       ← Sends email alerts and daily 7am digest
│   └── sms_sender.py         ← Sends instant SMS when conviction score ≥ 8
│
├── data/                     ← Stored signals, scores history (auto-populated)
├── logs/                     ← System logs (auto-populated)
├── tests/                    ← Unit tests for each module
│
├── main.py                   ← Entry point — runs the full pipeline
├── scheduler.py              ← Runs everything on a 30-min loop, 24/7
├── config.py                 ← Loads investor_profile.json + environment variables
├── requirements.txt          ← Python dependencies
├── Dockerfile                ← Containerizes the app for Railway
└── railway.toml              ← Railway deployment configuration
```

## How It Works

1. Every 30 minutes, **collectors** scan Reddit, news, and CEO social feeds
2. The **scoring engine** analyses sentiment, frequency, and sector alignment
3. Each stock gets a **conviction score (1–10)** and **risk tier**
4. If score ≥ 8 → **instant email + SMS alert**
5. Every day at 7am → **digest email** with top-scored stocks

## Quick Start

```bash
cp .env.example .env        # fill in your API keys
pip install -r requirements.txt
python main.py              # run once
python scheduler.py         # run continuously (for Railway)
```
