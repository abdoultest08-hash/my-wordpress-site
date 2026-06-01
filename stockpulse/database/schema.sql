-- =============================================================
-- StockPulse Database Schema
-- Run this entire file in: Supabase → SQL Editor → New query
-- =============================================================


-- =============================================================
-- 1. TICKERS
--    Master list of stocks StockPulse is tracking.
--    A ticker must exist here before signals or scores are stored.
-- =============================================================
CREATE TABLE IF NOT EXISTS tickers (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    symbol          TEXT NOT NULL UNIQUE,           -- e.g. "NVDA"
    name            TEXT NOT NULL,                  -- e.g. "NVIDIA Corporation"
    theme           TEXT,                           -- e.g. "AI / Semiconductors"
    sector          TEXT,                           -- e.g. "Technology"
    market_cap_tier TEXT CHECK (market_cap_tier IN (
                        'mega-cap', 'large-cap', 'mid-cap', 'small-cap', 'micro-cap'
                    )),
    watchlist_status TEXT NOT NULL DEFAULT 'active' CHECK (watchlist_status IN (
                        'active',      -- currently being monitored
                        'watching',    -- on radar, lighter monitoring
                        'paused',      -- temporarily ignored
                        'archived'     -- no longer tracked
                    )),
    added_at        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    notes           TEXT
);

COMMENT ON TABLE tickers IS 'Master list of stocks being monitored by StockPulse.';

-- Fast lookups by symbol and watchlist state
CREATE INDEX IF NOT EXISTS idx_tickers_symbol         ON tickers (symbol);
CREATE INDEX IF NOT EXISTS idx_tickers_watchlist      ON tickers (watchlist_status);
CREATE INDEX IF NOT EXISTS idx_tickers_theme          ON tickers (theme);


-- =============================================================
-- 2. SIGNALS
--    Raw intelligence collected from Reddit, news, and CEO posts.
--    Every mention of a ticker from any source lands here.
-- =============================================================
CREATE TABLE IF NOT EXISTS signals (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ticker          TEXT NOT NULL REFERENCES tickers (symbol) ON DELETE CASCADE,
    source          TEXT NOT NULL CHECK (source IN (
                        'reddit',
                        'news',
                        'ceo_social',
                        'sec_filing',
                        'manual'
                    )),
    source_detail   TEXT,           -- e.g. "r/wallstreetbets", "Reuters", "@elonmusk"
    content         TEXT NOT NULL,  -- the raw post / headline / tweet text
    url             TEXT,           -- link to original source
    sentiment       TEXT NOT NULL CHECK (sentiment IN (
                        'very_bullish',
                        'bullish',
                        'neutral',
                        'bearish',
                        'very_bearish'
                    )),
    sentiment_score NUMERIC(4,3)    -- -1.000 (most bearish) to +1.000 (most bullish)
                        CHECK (sentiment_score BETWEEN -1 AND 1),
    raw_score       NUMERIC(4,2)    -- intermediate 0–10 score before aggregation
                        CHECK (raw_score BETWEEN 0 AND 10),
    collected_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    source_published_at TIMESTAMPTZ -- when the original post/article was published
);

COMMENT ON TABLE signals IS 'Raw signals collected from every monitored source.';

CREATE INDEX IF NOT EXISTS idx_signals_ticker         ON signals (ticker);
CREATE INDEX IF NOT EXISTS idx_signals_source         ON signals (source);
CREATE INDEX IF NOT EXISTS idx_signals_collected_at   ON signals (collected_at DESC);
CREATE INDEX IF NOT EXISTS idx_signals_sentiment      ON signals (sentiment);


-- =============================================================
-- 3. CASCADE_EVENTS
--    Thematic cascades: a catalyst event that indirectly moves
--    other tickers. e.g. "SpaceX IPO announcement" raises the
--    conviction score of satellite component suppliers.
-- =============================================================
CREATE TABLE IF NOT EXISTS cascade_events (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    catalyst_ticker     TEXT REFERENCES tickers (symbol) ON DELETE SET NULL,
    catalyst_headline   TEXT NOT NULL,      -- the triggering news/event
    catalyst_source     TEXT,               -- where the catalyst was found
    affected_tickers    TEXT[] NOT NULL,    -- array of ticker symbols affected
    proximity_scores    JSONB,              -- {"MAXR": 0.85, "IRDM": 0.72} — how directly each is affected
    cascade_theme       TEXT,               -- e.g. "Space supply chain", "AI compute buildout"
    reasoning           TEXT,              -- why these tickers are connected to the catalyst
    detected_at         TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE cascade_events IS 'Indirect market effects — a catalyst and the downstream tickers it influences.';

CREATE INDEX IF NOT EXISTS idx_cascade_catalyst        ON cascade_events (catalyst_ticker);
CREATE INDEX IF NOT EXISTS idx_cascade_detected_at     ON cascade_events (detected_at DESC);
-- Index inside the JSONB column for fast proximity score lookups
CREATE INDEX IF NOT EXISTS idx_cascade_proximity       ON cascade_events USING GIN (proximity_scores);
-- Index inside the array for finding events that affect a specific ticker
CREATE INDEX IF NOT EXISTS idx_cascade_affected        ON cascade_events USING GIN (affected_tickers);


-- =============================================================
-- 4. DAILY_SCORES
--    One row per ticker per day — the final aggregated conviction
--    score and risk tier after all signals are processed.
--    This is what drives your 7am digest email.
-- =============================================================
CREATE TABLE IF NOT EXISTS daily_scores (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ticker              TEXT NOT NULL REFERENCES tickers (symbol) ON DELETE CASCADE,
    date                DATE NOT NULL DEFAULT CURRENT_DATE,
    conviction_score    NUMERIC(4,2) NOT NULL
                            CHECK (conviction_score BETWEEN 0 AND 10),
    risk_tier           TEXT NOT NULL CHECK (risk_tier IN (
                            'Low', 'Medium', 'High', 'Speculative'
                        )),
    signal_count        INTEGER DEFAULT 0,      -- how many signals fed into this score
    reddit_score        NUMERIC(4,2),           -- component sub-scores (0–10 each)
    news_score          NUMERIC(4,2),
    ceo_signal_score    NUMERIC(4,2),
    cascade_score       NUMERIC(4,2),
    sector_alignment    NUMERIC(4,2),           -- bonus for matching your preferred sectors
    reasoning           TEXT,                   -- plain-English explanation of the score
    prev_day_score      NUMERIC(4,2),           -- yesterday's score for trend tracking
    score_delta         NUMERIC(4,2)            -- conviction_score - prev_day_score
        GENERATED ALWAYS AS (conviction_score - prev_day_score) STORED,
    scored_at           TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Only one score row per ticker per day
    UNIQUE (ticker, date)
);

COMMENT ON TABLE daily_scores IS 'Daily aggregated conviction score and risk tier per ticker.';

CREATE INDEX IF NOT EXISTS idx_daily_ticker            ON daily_scores (ticker);
CREATE INDEX IF NOT EXISTS idx_daily_date              ON daily_scores (date DESC);
CREATE INDEX IF NOT EXISTS idx_daily_conviction        ON daily_scores (conviction_score DESC);
CREATE INDEX IF NOT EXISTS idx_daily_risk_tier         ON daily_scores (risk_tier);


-- =============================================================
-- 5. ALERTS
--    Audit log of every alert sent — instant (score ≥ 8) and
--    daily digest. Prevents duplicate sends and lets you review
--    your alert history.
-- =============================================================
CREATE TABLE IF NOT EXISTS alerts (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    ticker          TEXT NOT NULL REFERENCES tickers (symbol) ON DELETE CASCADE,
    alert_type      TEXT NOT NULL CHECK (alert_type IN (
                        'instant',      -- conviction score crossed threshold
                        'daily_digest', -- included in 7am digest email
                        'cascade',      -- triggered by a thematic cascade event
                        'risk_change'   -- risk tier changed up or down
                    )),
    conviction_score NUMERIC(4,2) NOT NULL,
    risk_tier       TEXT NOT NULL,
    trigger_reason  TEXT NOT NULL,      -- plain-English: why this alert fired
    channels_sent   TEXT[],            -- ["email", "sms"] — which channels were used
    email_sent      BOOLEAN DEFAULT FALSE,
    sms_sent        BOOLEAN DEFAULT FALSE,
    sent_at         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    acknowledged_at TIMESTAMPTZ,       -- optional: mark as reviewed
    cascade_event_id UUID REFERENCES cascade_events (id) ON DELETE SET NULL
);

COMMENT ON TABLE alerts IS 'Audit log of every alert sent — instant and daily digest.';

CREATE INDEX IF NOT EXISTS idx_alerts_ticker           ON alerts (ticker);
CREATE INDEX IF NOT EXISTS idx_alerts_sent_at          ON alerts (sent_at DESC);
CREATE INDEX IF NOT EXISTS idx_alerts_alert_type       ON alerts (alert_type);
CREATE INDEX IF NOT EXISTS idx_alerts_acknowledged     ON alerts (acknowledged_at) WHERE acknowledged_at IS NULL;


-- =============================================================
-- 6. PRICES
--    Latest live price per ticker, updated every pipeline run.
-- =============================================================
CREATE TABLE IF NOT EXISTS prices (
    ticker      TEXT PRIMARY KEY REFERENCES tickers (symbol) ON DELETE CASCADE,
    price       NUMERIC(12,4) NOT NULL,
    prev_close  NUMERIC(12,4),
    pct_change  NUMERIC(6,2),
    volume      BIGINT,
    fetched_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE prices IS 'Latest live price snapshot per ticker from Yahoo Finance.';


-- =============================================================
-- SEED DATA — starter tickers aligned to your investor profile
-- You can delete or extend this list at any time.
-- =============================================================
INSERT INTO tickers (symbol, name, theme, sector, market_cap_tier, watchlist_status) VALUES
    ('NVDA',  'NVIDIA Corporation',              'AI / Semiconductors',        'Technology',          'mega-cap',   'active'),
    ('AMD',   'Advanced Micro Devices',          'AI / Semiconductors',        'Technology',          'large-cap',  'active'),
    ('MSFT',  'Microsoft Corporation',           'AI / Cloud',                 'Technology',          'mega-cap',   'active'),
    ('PLTR',  'Palantir Technologies',           'AI / Defense Analytics',     'Technology',          'large-cap',  'active'),
    ('RKLB',  'Rocket Lab USA',                  'Space / Launch',             'Aerospace & Defense', 'small-cap',  'active'),
    ('ASTS',  'AST SpaceMobile',                 'Space / Connectivity',       'Telecommunications',  'small-cap',  'active'),
    ('IRDM',  'Iridium Communications',          'Space / Satellite',          'Telecommunications',  'mid-cap',    'active'),
    ('LMT',   'Lockheed Martin',                 'Defense / Aerospace',        'Aerospace & Defense', 'large-cap',  'watching'),
    ('ENPH',  'Enphase Energy',                  'Clean Tech / Solar',         'Energy',              'mid-cap',    'active'),
    ('FSLR',  'First Solar',                     'Clean Tech / Solar',         'Energy',              'mid-cap',    'active'),
    ('PLUG',  'Plug Power',                      'Clean Tech / Hydrogen',      'Energy',              'small-cap',  'watching'),
    ('TSLA',  'Tesla Inc',                       'EV / Energy / AI',           'Consumer Discretionary','mega-cap', 'active'),
    ('SMCI',  'Super Micro Computer',            'AI Infrastructure / Servers','Technology',          'large-cap',  'active'),
    ('ARM',   'Arm Holdings',                    'AI / Chip Architecture',     'Technology',          'large-cap',  'active')
ON CONFLICT (symbol) DO NOTHING;


-- =============================================================
-- HELPFUL VIEWS
-- These let you quickly query useful summaries.
-- =============================================================

-- Today's top conviction stocks
CREATE OR REPLACE VIEW v_top_stocks_today AS
SELECT
    ds.ticker,
    t.name,
    t.theme,
    t.market_cap_tier,
    ds.conviction_score,
    ds.risk_tier,
    ds.score_delta,
    ds.signal_count,
    ds.reasoning
FROM daily_scores ds
JOIN tickers t ON t.symbol = ds.ticker
WHERE ds.date = CURRENT_DATE
ORDER BY ds.conviction_score DESC;

-- Unacknowledged alerts (your inbox of things to review)
CREATE OR REPLACE VIEW v_pending_alerts AS
SELECT
    a.ticker,
    t.name,
    a.alert_type,
    a.conviction_score,
    a.risk_tier,
    a.trigger_reason,
    a.channels_sent,
    a.sent_at
FROM alerts a
JOIN tickers t ON t.symbol = a.ticker
WHERE a.acknowledged_at IS NULL
ORDER BY a.sent_at DESC;

-- Recent cascade events with affected ticker details
CREATE OR REPLACE VIEW v_recent_cascades AS
SELECT
    ce.catalyst_headline,
    ce.catalyst_ticker,
    ce.affected_tickers,
    ce.cascade_theme,
    ce.reasoning,
    ce.proximity_scores,
    ce.detected_at
FROM cascade_events ce
ORDER BY ce.detected_at DESC
LIMIT 50;
