"""
Database connection manager.

- Local development: SQLite (zero setup, file-based)
- Railway deployment: PostgreSQL (injected via DATABASE_URL env var automatically)

Every module imports `get_db()` to get a connection.
"""

import os
import sqlite3
from contextlib import contextmanager
from pathlib import Path
from dotenv import load_dotenv

# .env lives in stockpulse/ (parent of database/)
load_dotenv(Path(__file__).parent.parent / ".env")

DATABASE_URL = os.getenv("DATABASE_URL", "")
_IS_POSTGRES = DATABASE_URL.startswith("postgres")

# SQLite file path used in local development
_SQLITE_PATH = Path(__file__).parent / "data" / "stockpulse.db"
_SQLITE_PATH.parent.mkdir(exist_ok=True)


def _get_postgres_conn():
    import psycopg2
    import psycopg2.extras
    conn = psycopg2.connect(DATABASE_URL, sslmode="require")
    conn.autocommit = False
    return conn


def _get_sqlite_conn():
    conn = sqlite3.connect(_SQLITE_PATH)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA journal_mode=WAL")
    return conn


@contextmanager
def get_db():
    """Yield an open database connection, auto-commit on success, rollback on error."""
    conn = _get_postgres_conn() if _IS_POSTGRES else _get_sqlite_conn()
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()


def execute(sql: str, params: tuple = ()) -> list[dict]:
    """Run a single SQL statement and return all rows as dicts."""
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(sql, params)
        if cur.description:
            cols = [d[0] for d in cur.description]
            if _IS_POSTGRES:
                return [dict(zip(cols, row)) for row in cur.fetchall()]
            else:
                return [dict(row) for row in cur.fetchall()]
        return []


def executemany(sql: str, params_list: list[tuple]) -> None:
    """Run a SQL statement for each item in params_list."""
    with get_db() as conn:
        cur = conn.cursor()
        cur.executemany(sql, params_list)


def insert(table: str, data: dict) -> None:
    """Insert a single row dict into a table."""
    cols = ", ".join(data.keys())
    if _IS_POSTGRES:
        placeholders = ", ".join(f"%s" for _ in data)
    else:
        placeholders = ", ".join("?" for _ in data)
    sql = f"INSERT INTO {table} ({cols}) VALUES ({placeholders})"
    with get_db() as conn:
        conn.cursor().execute(sql, tuple(data.values()))


def upsert_daily_score(row: dict) -> None:
    """Insert or update a daily_scores row (one per ticker per date)."""
    if _IS_POSTGRES:
        sql = """
            INSERT INTO daily_scores
                (ticker, date, conviction_score, risk_tier, signal_count,
                 reddit_score, news_score, ceo_signal_score, cascade_score,
                 sector_alignment, reasoning, prev_day_score)
            VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
            ON CONFLICT (ticker, date) DO UPDATE SET
                conviction_score  = EXCLUDED.conviction_score,
                risk_tier         = EXCLUDED.risk_tier,
                signal_count      = EXCLUDED.signal_count,
                reddit_score      = EXCLUDED.reddit_score,
                news_score        = EXCLUDED.news_score,
                ceo_signal_score  = EXCLUDED.ceo_signal_score,
                cascade_score     = EXCLUDED.cascade_score,
                sector_alignment  = EXCLUDED.sector_alignment,
                reasoning         = EXCLUDED.reasoning,
                scored_at         = NOW()
        """
    else:
        sql = """
            INSERT INTO daily_scores
                (ticker, date, conviction_score, risk_tier, signal_count,
                 reddit_score, news_score, ceo_signal_score, cascade_score,
                 sector_alignment, reasoning, prev_day_score)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            ON CONFLICT (ticker, date) DO UPDATE SET
                conviction_score  = excluded.conviction_score,
                risk_tier         = excluded.risk_tier,
                signal_count      = excluded.signal_count,
                reddit_score      = excluded.reddit_score,
                news_score        = excluded.news_score,
                ceo_signal_score  = excluded.ceo_signal_score,
                cascade_score     = excluded.cascade_score,
                sector_alignment  = excluded.sector_alignment,
                reasoning         = excluded.reasoning,
                scored_at         = datetime('now')
        """
    with get_db() as conn:
        conn.cursor().execute(sql, (
            row["ticker"], row["date"], row["conviction_score"], row["risk_tier"],
            row.get("signal_count", 0), row.get("reddit_score"),
            row.get("news_score"), row.get("ceo_signal_score"),
            row.get("cascade_score"), row.get("sector_alignment"),
            row.get("reasoning"), row.get("prev_day_score"),
        ))


def upsert_price(row: dict) -> None:
    """Insert or update a prices row (one per ticker, latest price)."""
    if _IS_POSTGRES:
        sql = """
            INSERT INTO prices (ticker, price, prev_close, pct_change, volume, fetched_at)
            VALUES (%s,%s,%s,%s,%s,%s)
            ON CONFLICT (ticker) DO UPDATE SET
                price      = EXCLUDED.price,
                prev_close = EXCLUDED.prev_close,
                pct_change = EXCLUDED.pct_change,
                volume     = EXCLUDED.volume,
                fetched_at = EXCLUDED.fetched_at
        """
    else:
        sql = """
            INSERT INTO prices (ticker, price, prev_close, pct_change, volume, fetched_at)
            VALUES (?,?,?,?,?,?)
            ON CONFLICT (ticker) DO UPDATE SET
                price      = excluded.price,
                prev_close = excluded.prev_close,
                pct_change = excluded.pct_change,
                volume     = excluded.volume,
                fetched_at = excluded.fetched_at
        """
    with get_db() as conn:
        conn.cursor().execute(sql, (
            row["ticker"], row["price"], row["prev_close"],
            row["pct_change"], row["volume"], row["fetched_at"],
        ))


def is_postgres() -> bool:
    return _IS_POSTGRES
