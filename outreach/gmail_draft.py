"""
gmail_draft.py
Creates a Gmail draft with the mock site screenshot embedded in the email body.
Requires Gmail API credentials (credentials.json in this folder).

Setup (one-time):
  1. Go to console.cloud.google.com → New project
  2. Enable Gmail API
  3. Create OAuth 2.0 Desktop credentials → download as credentials.json
  4. Run this file once → browser opens → sign in → token.json saved for future runs
"""
import base64
import json
import os
import requests
from pathlib import Path
from email.mime.multipart import MIMEMultipart
from email.mime.text      import MIMEText
from email.mime.image     import MIMEImage

CREDS_FILE = Path(__file__).parent / "credentials.json"
TOKEN_FILE  = Path(__file__).parent / "token.json"

def get_access_token_for_account(account: dict) -> str:
    """Get access token for a specific account dict."""
    from accounts import get_access_token
    return get_access_token(account)


def get_access_token() -> str:
    """Return a valid access token, refreshing if needed."""
    token_data = json.loads(TOKEN_FILE.read_text())
    creds_data  = json.loads(CREDS_FILE.read_text())["installed"]

    if "refresh_token" in token_data:
        r = requests.post("https://oauth2.googleapis.com/token", data={
            "client_id":     creds_data["client_id"],
            "client_secret": creds_data["client_secret"],
            "refresh_token": token_data["refresh_token"],
            "grant_type":    "refresh_token",
        })
        if r.status_code == 200:
            token_data["access_token"] = r.json()["access_token"]
            TOKEN_FILE.write_text(json.dumps(token_data))

    return token_data.get("access_token") or token_data.get("token")


def build_subject(lead: dict) -> str:
    """Returns the email subject line."""
    return f"Question for {lead['business_name']}"


def build_email_body(lead: dict, your_name: str, your_website: str, copy_version: str = "v1") -> str:
    """
    Returns the HTML email body.
    copy_version is logged to CRM for A/B tracking.
    pitch_type: 'new' = no website, 'upgrade' = has website.
    """
    biz        = lead["business_name"]
    industry   = lead.get("industry", "service businesses")
    city       = lead.get("city", "your area")
    pitch_type = lead.get("pitch_type", "new")

    if pitch_type == "upgrade":
        para1 = f"Heard about {biz} from someone the other day. They said your work is solid but the website doesn't really reflect that."
        para2 = "I put together a quick mock-up of what an updated version could look like."
        para3 = f"Most {industry}s in your area pick up 5+ extra quote requests a month just from Google."
    else:
        para1 = f"Heard about {biz} from someone the other day. They said your work is solid but you don't have a website yet, which means you're probably missing out on quote requests every month."
        para2 = "I put together a quick mock-up of what one could look like for you."
        para3 = f"Most {industry}s in your area pick up 5+ extra quote requests a month just from Google."

    return f"""<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;color:#222;font-size:15px;line-height:1.8">

<p>Hi there,</p>

<p>{para1}</p>

<p>{para2}</p>

<div style="border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10);margin:20px 0">
  <img src="cid:mocksite_preview" alt="{biz} Website Preview" style="width:100%;display:block">
</div>

<p>{para3}</p>

<p>Worth a look? Would love to know what you think.</p>

<p style="margin-top:28px">
  Abdoul Sandwidi
</p>

</div><!-- cv:{copy_version} pt:{pitch_type} -->"""


def _build_mime(lead: dict, screenshot_path: str, from_name: str, from_email: str,
                your_website: str, copy_version: str = "v1") -> MIMEMultipart:
    """Shared MIME builder for send and draft."""
    msg = MIMEMultipart("related")
    msg["Subject"] = build_subject(lead)
    msg["From"]    = f"{from_name} <{from_email}>"
    msg["To"]      = lead["email"]
    msg.attach(MIMEText(build_email_body(lead, from_name, your_website, copy_version), "html"))
    with open(screenshot_path, "rb") as f:
        img = MIMEImage(f.read(), _subtype="png")
    img.add_header("Content-ID",          "<mocksite_preview>")
    img.add_header("Content-Disposition", "inline", filename="website_preview.png")
    msg.attach(img)
    return msg


def send_email(lead: dict, screenshot_path: str, account: dict, your_website: str,
               copy_version: str = "v1") -> str:
    """Sends an email immediately from the given account. Returns Gmail message ID."""
    msg   = _build_mime(lead, screenshot_path, account["name"], account["email"],
                        your_website, copy_version)
    raw   = base64.urlsafe_b64encode(msg.as_bytes()).decode()
    token = get_access_token_for_account(account)
    resp  = requests.post(
        "https://gmail.googleapis.com/gmail/v1/users/me/messages/send",
        headers={"Authorization": f"Bearer {token}"},
        json={"raw": raw},
    )
    resp.raise_for_status()
    return resp.json()["id"]


def create_draft(lead: dict, screenshot_path: str, your_name: str, your_email: str,
                 your_website: str, copy_version: str = "v1", account: dict | None = None) -> str:
    """Creates a Gmail draft in the given account's mailbox. Returns draft ID."""
    msg  = _build_mime(lead, screenshot_path, your_name, your_email,
                       your_website, copy_version)
    raw  = base64.urlsafe_b64encode(msg.as_bytes()).decode()
    token = get_access_token_for_account(account) if account else get_access_token()
    resp = requests.post(
        "https://gmail.googleapis.com/gmail/v1/users/me/drafts",
        headers={"Authorization": f"Bearer {token}"},
        json={"message": {"raw": raw}},
    )
    resp.raise_for_status()
    return resp.json()["id"]


if __name__ == "__main__":
    print("Gmail draft module loaded. Run pipeline.py to use it.")
