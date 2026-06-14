"""
gmail_auth.py
Run this once on your Mac to authenticate each Gmail account.
It will open a browser window for you to log in and grant permission.
Creates token.json and token_pagesforlocals.json in the outreach/ folder.
"""
import os
import json
from pathlib import Path
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials

SCOPES = [
    "https://www.googleapis.com/auth/gmail.send",
    "https://www.googleapis.com/auth/gmail.compose",
    "https://mail.google.com/",
]

BASE = Path(__file__).parent

ACCOUNTS = [
    {"email": "sitesbyabs@gmail.com",     "token_file": BASE / "token.json"},
    {"email": "pagesforlocals@gmail.com",  "token_file": BASE / "token_pagesforlocals.json"},
]

def authenticate(account):
    creds_file = BASE / "credentials.json"
    if not creds_file.exists():
        print(f"\n❌ credentials.json not found in {BASE}")
        print("Download it from Google Cloud Console → APIs & Services → Credentials → OAuth 2.0 Client → Download JSON")
        return False

    token_file = account["token_file"]
    creds = None

    if token_file.exists():
        creds = Credentials.from_authorized_user_file(str(token_file), SCOPES)

    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            print(f"\n🌐 Opening browser for: {account['email']}")
            print("Log in with that Gmail account when the browser opens...\n")
            flow = InstalledAppFlow.from_client_secrets_file(str(creds_file), SCOPES)
            creds = flow.run_local_server(port=0)

        with open(token_file, "w") as f:
            f.write(creds.to_json())
        print(f"✅ Token saved: {token_file.name}")

    return True

if __name__ == "__main__":
    print("Gmail Authentication Tool")
    print("=" * 40)
    for acc in ACCOUNTS:
        print(f"\nAuthenticating: {acc['email']}")
        authenticate(acc)
    print("\n✅ All accounts authenticated! You can now run the pipeline.")
