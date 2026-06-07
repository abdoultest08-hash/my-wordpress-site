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

    return token_data["access_token"]


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
    owner      = lead.get("owner_name") or "there"
    city       = lead.get("city", "your area")
    industry   = lead.get("industry", "service")
    pitch_type = lead.get("pitch_type", "new")

    if pitch_type == "upgrade":
        referral_line = f"""Someone actually mentioned {biz} to me the other day — said you do great work
    but that your website doesn't really do you justice. So I decided to put together a quick
    <strong>free preview</strong> of what a modern site could look like for you."""
        value_line = """A site like this typically brings in <strong>5+ extra quote requests per month</strong>
    just from people finding you on Google — without any ads."""
        cta_line = "Does this feel like the right direction for your business?"
    else:
        referral_line = f"""A family member of mine recently used a {industry} in {city} and mentioned
    {biz} — said you do great work but don't have a website. So I decided to put together a
    <strong>free preview</strong> of what one could look like for you."""
        value_line = """For local {industry} businesses, a site like this typically brings in
    <strong>5+ extra quote requests per month</strong> — just from people searching on Google
    in your area, without spending anything on ads."""
        cta_line = "Does this feel like the right fit for your business?"

    return f"""
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;color:#1A2533;line-height:1.7">

  <p style="font-size:15px;margin-bottom:6px">Hi {owner},</p>

  <p style="font-size:15px">{referral_line}</p>

  <p style="font-size:15px;margin-bottom:4px">Here's what I put together:</p>

  <div style="border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.13);margin:18px 0 22px">
    <img src="cid:mocksite_preview" alt="{biz} Website Preview"
         style="width:100%;display:block">
  </div>

  <p style="font-size:15px">{value_line}</p>

  <p style="font-size:15px">
    I'd love to get your thoughts on it — no pitch, no pressure. Just curious whether this
    is something that would be useful to you.
  </p>

  <p style="font-size:15px;font-weight:600">{cta_line}</p>

  <a href="mailto:{lead.get('email','')}"
     style="display:inline-block;background:#F5A623;color:#0D1B2A;font-weight:700;
            padding:13px 26px;border-radius:6px;text-decoration:none;font-size:15px;margin:8px 0 24px">
    👉 Yes, tell me more
  </a>

  <p style="font-size:13px;color:#999;margin-top:28px;border-top:1px solid #eee;padding-top:16px">
    {your_name}<br>
    Web Design for Local Businesses · <a href="https://{your_website}" style="color:#999">{your_website}</a>
  </p>

</div>
<!-- cv:{copy_version} pt:{pitch_type} -->
"""


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
                 your_website: str, copy_version: str = "v1") -> str:
    """Creates a Gmail draft. Returns draft ID."""
    msg  = _build_mime(lead, screenshot_path, your_name, your_email,
                       your_website, copy_version)
    raw  = base64.urlsafe_b64encode(msg.as_bytes()).decode()
    # Refresh token for default account
    token_data  = json.loads((Path(__file__).parent / "token.json").read_text())
    creds_data  = json.loads((Path(__file__).parent / "credentials.json").read_text())["installed"]
    r = requests.post("https://oauth2.googleapis.com/token", data={
        "client_id":     creds_data["client_id"],
        "client_secret": creds_data["client_secret"],
        "refresh_token": token_data["refresh_token"],
        "grant_type":    "refresh_token",
    })
    token = r.json().get("access_token", token_data["access_token"])
    resp = requests.post(
        "https://gmail.googleapis.com/gmail/v1/users/me/drafts",
        headers={"Authorization": f"Bearer {token}"},
        json={"message": {"raw": raw}},
    )
    resp.raise_for_status()
    return resp.json()["id"]


if __name__ == "__main__":
    print("Gmail draft module loaded. Run pipeline.py to use it.")
