#!/usr/bin/env python3
"""
Run this once to authorize Gmail access.
After this, token.json is saved and pipeline.py runs without any browser prompts.

Usage:
  cd outreach
  python3 authorize_gmail.py
"""
import os, sys
from pathlib import Path

CREDS_FILE = Path(__file__).parent / "credentials.json"
TOKEN_FILE  = Path(__file__).parent / "token.json"
SCOPES      = ["https://www.googleapis.com/auth/gmail.compose"]

if not CREDS_FILE.exists():
    print("❌ credentials.json not found in this folder.")
    sys.exit(1)

from google_auth_oauthlib.flow import InstalledAppFlow

flow = InstalledAppFlow.from_client_secrets_file(str(CREDS_FILE), SCOPES)
flow.redirect_uri = "urn:ietf:wg:oauth:2.0:oob"

auth_url, _ = flow.authorization_url(prompt="consent", access_type="offline")

print("\n" + "="*65)
print("STEP 1 — Open this URL in your browser")
print("         (make sure you're logged in as sitesbyabs@gmail.com)")
print("="*65)
print(f"\n{auth_url}\n")
print("="*65)
print("STEP 2 — Google will show you a code. Copy it and paste below.")
print("="*65 + "\n")

code = input("Paste authorization code: ").strip()

flow.fetch_token(code=code)
creds = flow.credentials

TOKEN_FILE.write_text(creds.to_json())
print(f"\n✅ Authorization successful! token.json saved.")
print("   You can now run: python3 pipeline.py --no-gmail (test) or python3 pipeline.py")
