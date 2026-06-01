"""
Run once to initialise the database.

  python database/init_db.py

- Local: creates stockpulse/data/stockpulse.db (SQLite)
- Railway: runs schema.sql against the PostgreSQL DATABASE_URL
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import get_db, is_postgres

_HERE = Path(__file__).parent
_SCHEMA_PG     = _HERE / "schema.sql"
_SCHEMA_SQLITE = _HERE / "schema_sqlite.sql"


def init():
    schema_file = _SCHEMA_PG if is_postgres() else _SCHEMA_SQLITE
    sql = schema_file.read_text()

    db_type = "PostgreSQL" if is_postgres() else "SQLite"
    print(f"Initialising {db_type} database...")

    with get_db() as conn:
        cur = conn.cursor()
        # Execute each statement separately (both drivers need this)
        statements = [s.strip() for s in sql.split(";") if s.strip()]
        for stmt in statements:
            try:
                cur.execute(stmt)
            except Exception as e:
                # Skip "already exists" errors on re-runs
                if "already exists" in str(e).lower() or "duplicate" in str(e).lower():
                    continue
                print(f"  Warning: {e}")

    print("Done. Verifying tables...")

    from database.db import execute
    rows = execute("SELECT symbol, name FROM tickers ORDER BY symbol")
    print(f"  tickers: {len(rows)} rows")
    for r in rows:
        print(f"    {r['symbol']:6s}  {r['name']}")

    print("\nDatabase ready.")


if __name__ == "__main__":
    init()
