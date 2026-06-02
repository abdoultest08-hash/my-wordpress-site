"""
Run once to initialise the database, and on every restart to add new tickers.

- Local: creates stockpulse/data/stockpulse.db (SQLite)
- Railway: runs schema.sql against the PostgreSQL DATABASE_URL
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent.parent))

from database.db import get_db, is_postgres, execute

_HERE = Path(__file__).parent
_SCHEMA_PG     = _HERE / "schema.sql"
_SCHEMA_SQLITE = _HERE / "schema_sqlite.sql"

# Full ticker list — always synced on startup so new tickers are never missed
ALL_TICKERS = [
    # AI / Semiconductors / Big Tech
    ("NVDA", "NVIDIA Corporation",           "AI / Semiconductors",        "Technology",             "mega-cap",  "active"),
    ("AMD",  "Advanced Micro Devices",        "AI / Semiconductors",        "Technology",             "large-cap", "active"),
    ("MSFT", "Microsoft Corporation",         "AI / Cloud",                 "Technology",             "mega-cap",  "active"),
    ("AAPL", "Apple Inc",                     "Big Tech / Consumer",        "Technology",             "mega-cap",  "active"),
    ("META", "Meta Platforms",                "AI / Social Media",          "Technology",             "mega-cap",  "active"),
    ("GOOGL","Alphabet Inc",                  "AI / Cloud / Search",        "Technology",             "mega-cap",  "active"),
    ("ASML", "ASML Holding",                  "Semiconductor Equipment",    "Technology",             "mega-cap",  "active"),
    ("PLTR", "Palantir Technologies",         "AI / Defense Analytics",     "Technology",             "large-cap", "active"),
    ("ARM",  "Arm Holdings",                  "AI / Chip Architecture",     "Technology",             "large-cap", "active"),
    ("SMCI", "Super Micro Computer",          "AI Infrastructure",          "Technology",             "large-cap", "active"),
    ("MU",   "Micron Technology",             "AI / Memory Chips",          "Technology",             "large-cap", "active"),
    ("IONQ", "IonQ",                          "Quantum Computing",          "Technology",             "small-cap", "active"),
    # Space / Defense / Aerospace
    ("RKLB", "Rocket Lab USA",                "Space / Launch",             "Aerospace & Defense",    "small-cap", "active"),
    ("ASTS", "AST SpaceMobile",               "Space / Connectivity",       "Telecommunications",     "small-cap", "active"),
    ("IRDM", "Iridium Communications",        "Space / Satellite",          "Telecommunications",     "mid-cap",   "active"),
    ("LMT",  "Lockheed Martin",               "Defense / Aerospace",        "Aerospace & Defense",    "large-cap", "active"),
    ("KTOS", "Kratos Defense & Security",     "Space / Defense Tech",       "Aerospace & Defense",    "mid-cap",   "active"),
    ("BWXT", "BWX Technologies",              "Space / Nuclear Defense",    "Aerospace & Defense",    "mid-cap",   "active"),
    # Data Centers / Cooling
    ("VRT",  "Vertiv Holdings",               "Data Center Cooling",        "Technology",             "large-cap", "active"),
    ("EQIX", "Equinix",                       "Data Centers / REIT",        "Real Estate",            "mega-cap",  "active"),
    ("DLR",  "Digital Realty Trust",          "Data Centers / REIT",        "Real Estate",            "large-cap", "active"),
    ("OKLO", "Oklo",                          "Nuclear / Data Center Power","Energy",                 "small-cap", "active"),
    ("CEG",  "Constellation Energy",          "Nuclear Energy",             "Energy",                 "large-cap", "active"),
    # Clean Energy / Batteries
    ("FLNC", "Fluence Energy",                "Clean Tech / Energy Storage","Energy",                 "mid-cap",   "active"),
    ("ENPH", "Enphase Energy",                "Clean Tech / Solar",         "Energy",                 "mid-cap",   "active"),
    ("FSLR", "First Solar",                   "Clean Tech / Solar",         "Energy",                 "mid-cap",   "active"),
    ("PLUG", "Plug Power",                    "Clean Tech / Hydrogen",      "Energy",                 "small-cap", "active"),
    ("TSLA", "Tesla Inc",                     "EV / Energy / AI",           "Consumer Discretionary", "mega-cap",  "active"),
    ("ENVX", "Enovix Corporation",            "Electric Batteries",         "Technology",             "small-cap", "active"),
    ("QS",   "QuantumScape",                  "Solid-State Batteries",      "Technology",             "small-cap", "active"),
    ("BE",   "Bloom Energy",                  "Clean Tech / Fuel Cell",     "Energy",                 "mid-cap",   "active"),
    # Biotech
    ("MRNA", "Moderna",                       "Biotech / mRNA",             "Healthcare",             "large-cap", "active"),
    ("CRSP", "CRISPR Therapeutics",           "Biotech / Gene Editing",     "Healthcare",             "mid-cap",   "active"),
    ("ILMN", "Illumina",                      "Biotech / Genomics",         "Healthcare",             "large-cap", "active"),
    ("RXRX", "Recursion Pharmaceuticals",     "AI Biotech",                 "Healthcare",             "small-cap", "active"),
    # Logistics
    ("FDX",  "FedEx Corporation",             "Logistics / Freight",        "Industrials",            "large-cap", "active"),
    ("XPO",  "XPO Logistics",                 "Logistics / Freight",        "Industrials",            "mid-cap",   "active"),
    ("SAIA", "Saia Inc",                      "Logistics / LTL Freight",    "Industrials",            "mid-cap",   "active"),
    # Macro virtual ticker
    ("MACRO","Global Macro Events",           "Macro / World Events",       "Global",                 "mega-cap",  "watching"),
]


def init():
    schema_file = _SCHEMA_PG if is_postgres() else _SCHEMA_SQLITE
    sql = schema_file.read_text()

    db_type = "PostgreSQL" if is_postgres() else "SQLite"
    print(f"Initialising {db_type} database...")

    with get_db() as conn:
        cur = conn.cursor()
        statements = [s.strip() for s in sql.split(";") if s.strip()]
        for stmt in statements:
            try:
                cur.execute(stmt)
            except Exception as e:
                if "already exists" in str(e).lower() or "duplicate" in str(e).lower():
                    continue
                print(f"  Warning: {e}")

    # Always sync full ticker list — adds new tickers without touching existing ones
    _sync_tickers()

    print("Done. Verifying tables...")
    rows = execute("SELECT symbol, name FROM tickers ORDER BY symbol")
    print(f"  tickers: {len(rows)} rows")
    for r in rows:
        print(f"    {r['symbol']:6s}  {r['name']}")
    print("\nDatabase ready.")


def _sync_tickers():
    """Insert any missing tickers from ALL_TICKERS without overwriting existing rows."""
    ph = "%s" if is_postgres() else "?"
    sql = f"""
        INSERT INTO tickers (symbol, name, theme, sector, market_cap_tier, watchlist_status)
        VALUES ({ph},{ph},{ph},{ph},{ph},{ph})
        ON CONFLICT (symbol) DO NOTHING
    """
    added = 0
    with get_db() as conn:
        cur = conn.cursor()
        for row in ALL_TICKERS:
            try:
                cur.execute(sql, row)
                added += 1
            except Exception as e:
                if "conflict" not in str(e).lower() and "duplicate" not in str(e).lower():
                    print(f"  Warning adding {row[0]}: {e}")
    print(f"  Ticker sync: {len(ALL_TICKERS)} tickers ensured in DB")


if __name__ == "__main__":
    init()
