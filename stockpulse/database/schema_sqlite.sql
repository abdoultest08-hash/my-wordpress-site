-- SQLite schema for local development.
-- Railway PostgreSQL uses schema.sql (which has UUID, TIMESTAMPTZ, etc.)
-- SQLite uses simpler types but identical structure.

CREATE TABLE IF NOT EXISTS tickers (
    id              TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
    symbol          TEXT NOT NULL UNIQUE,
    name            TEXT NOT NULL,
    theme           TEXT,
    sector          TEXT,
    market_cap_tier TEXT CHECK (market_cap_tier IN ('mega-cap','large-cap','mid-cap','small-cap','micro-cap')),
    watchlist_status TEXT NOT NULL DEFAULT 'active' CHECK (watchlist_status IN ('active','watching','paused','archived')),
    added_at        TEXT NOT NULL DEFAULT (datetime('now')),
    notes           TEXT
);

CREATE TABLE IF NOT EXISTS signals (
    id                  TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
    ticker              TEXT NOT NULL REFERENCES tickers(symbol) ON DELETE CASCADE,
    source              TEXT NOT NULL CHECK (source IN ('reddit','news','ceo_social','sec_filing','manual')),
    source_detail       TEXT,
    content             TEXT NOT NULL,
    url                 TEXT,
    sentiment           TEXT NOT NULL CHECK (sentiment IN ('very_bullish','bullish','neutral','bearish','very_bearish')),
    sentiment_score     REAL CHECK (sentiment_score BETWEEN -1 AND 1),
    raw_score           REAL CHECK (raw_score BETWEEN 0 AND 10),
    collected_at        TEXT NOT NULL DEFAULT (datetime('now')),
    source_published_at TEXT
);

CREATE TABLE IF NOT EXISTS cascade_events (
    id                  TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
    catalyst_ticker     TEXT REFERENCES tickers(symbol) ON DELETE SET NULL,
    catalyst_headline   TEXT NOT NULL,
    catalyst_source     TEXT,
    affected_tickers    TEXT NOT NULL,  -- JSON array stored as text e.g. '["IRDM","RKLB"]'
    proximity_scores    TEXT,           -- JSON object e.g. '{"IRDM": 0.85}'
    cascade_theme       TEXT,
    reasoning           TEXT,
    detected_at         TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS daily_scores (
    id                  TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
    ticker              TEXT NOT NULL REFERENCES tickers(symbol) ON DELETE CASCADE,
    date                TEXT NOT NULL DEFAULT (date('now')),
    conviction_score    REAL NOT NULL CHECK (conviction_score BETWEEN 0 AND 10),
    risk_tier           TEXT NOT NULL CHECK (risk_tier IN ('Low','Medium','High','Speculative')),
    signal_count        INTEGER DEFAULT 0,
    reddit_score        REAL,
    news_score          REAL,
    ceo_signal_score    REAL,
    cascade_score       REAL,
    sector_alignment    REAL,
    reasoning           TEXT,
    prev_day_score      REAL,
    scored_at           TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (ticker, date)
);

CREATE TABLE IF NOT EXISTS alerts (
    id              TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
    ticker          TEXT NOT NULL REFERENCES tickers(symbol) ON DELETE CASCADE,
    alert_type      TEXT NOT NULL CHECK (alert_type IN ('instant','daily_digest','cascade','risk_change')),
    conviction_score REAL NOT NULL,
    risk_tier       TEXT NOT NULL,
    trigger_reason  TEXT NOT NULL,
    channels_sent   TEXT,   -- JSON array e.g. '["email","sms"]'
    email_sent      INTEGER DEFAULT 0,
    sms_sent        INTEGER DEFAULT 0,
    sent_at         TEXT NOT NULL DEFAULT (datetime('now')),
    acknowledged_at TEXT,
    cascade_event_id TEXT REFERENCES cascade_events(id) ON DELETE SET NULL
);

-- Seed tickers
INSERT OR IGNORE INTO tickers (symbol, name, theme, sector, market_cap_tier, watchlist_status) VALUES
    ('NVDA',  'NVIDIA Corporation',           'AI / Semiconductors',     'Technology',          'mega-cap',  'active'),
    ('AMD',   'Advanced Micro Devices',        'AI / Semiconductors',     'Technology',          'large-cap', 'active'),
    ('MSFT',  'Microsoft Corporation',         'AI / Cloud',              'Technology',          'mega-cap',  'active'),
    ('PLTR',  'Palantir Technologies',         'AI / Defense Analytics',  'Technology',          'large-cap', 'active'),
    ('RKLB',  'Rocket Lab USA',                'Space / Launch',          'Aerospace & Defense', 'small-cap', 'active'),
    ('ASTS',  'AST SpaceMobile',               'Space / Connectivity',    'Telecommunications',  'small-cap', 'active'),
    ('IRDM',  'Iridium Communications',        'Space / Satellite',       'Telecommunications',  'mid-cap',   'active'),
    ('LMT',   'Lockheed Martin',               'Defense / Aerospace',     'Aerospace & Defense', 'large-cap', 'watching'),
    ('ENPH',  'Enphase Energy',                'Clean Tech / Solar',      'Energy',              'mid-cap',   'active'),
    ('FSLR',  'First Solar',                   'Clean Tech / Solar',      'Energy',              'mid-cap',   'active'),
    ('PLUG',  'Plug Power',                    'Clean Tech / Hydrogen',   'Energy',              'small-cap', 'watching'),
    ('TSLA',  'Tesla Inc',                     'EV / Energy / AI',        'Consumer Discretionary','mega-cap','active'),
    ('SMCI',  'Super Micro Computer',          'AI Infrastructure',       'Technology',          'large-cap', 'active'),
    ('ARM',   'Arm Holdings',                  'AI / Chip Architecture',  'Technology',          'large-cap', 'active');
