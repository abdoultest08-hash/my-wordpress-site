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


def build_email_body(lead: dict, your_name: str, your_website: str) -> str:
    """Returns the HTML email body. Switches copy based on pitch_type."""
    biz        = lead["business_name"]
    owner      = lead.get("owner_name") or "there"
    city       = lead.get("city", "your city")
    industry   = lead.get("industry", "service")
    pitch_type = lead.get("pitch_type", "new")  # "new" or "upgrade"

    if pitch_type == "upgrade":
        intro = f"""I was browsing {industry} companies in {city} and came across <strong>{biz}</strong>.
    Your current site caught my eye — I could see straight away there's a big opportunity to make it
    work a lot harder for you. So I went ahead and mocked up a <strong>free redesign</strong> to show
    you what it could look like."""
        bullet1 = "✅ Rank higher on Google with a faster, modern site"
        bullet2 = "✅ Convert more visitors into calls and enquiries"
        bullet3 = "✅ Look more professional than your local competitors"
        subject_suffix = "— free redesign concept"
    else:
        intro = f"""I was searching for {industry} companies in {city} and came across
    <strong>{biz}</strong>. I noticed you don't have a website yet — so I went ahead and built
    a <strong>free mock homepage</strong> to show you what it could look like."""
        bullet1 = "✅ Show up on Google when locals search for you"
        bullet2 = "✅ Look more professional than competitors"
        bullet3 = "✅ Get more calls and enquiries automatically"

    return f"""
<div style="font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#1A2533">
  <p style="font-size:15px">Hi {owner},</p>

  <p style="font-size:15px;line-height:1.7">{intro}</p>

  <p style="font-size:15px;line-height:1.7">Here's a preview:</p>

  <div style="border-radius:10px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.15);margin:20px 0">
    <img src="cid:mocksite_preview" alt="{biz} Website Preview"
         style="width:100%;display:block;border-radius:10px">
  </div>

  <p style="font-size:15px;line-height:1.7">
    This is just a starting point — I can customise it fully with your services, photos,
    reviews, and anything else you'd like. A great website helps you:
  </p>
  <ul style="font-size:15px;line-height:2">
    <li>{bullet1}</li>
    <li>{bullet2}</li>
    <li>{bullet3}</li>
  </ul>

  <p style="font-size:15px;line-height:1.7">
    I'd love to jump on a quick 15-minute call to walk you through it — completely free,
    no pressure. Would any time this week work for you?
  </p>

  <a href="tel:{lead.get('phone','')}"
     style="display:inline-block;background:#F5A623;color:#0D1B2A;font-weight:700;
            padding:14px 28px;border-radius:6px;text-decoration:none;font-size:15px;margin:8px 0">
    👉 Reply or Call to Learn More
  </a>

  <p style="font-size:14px;color:#888;margin-top:32px;border-top:1px solid #eee;padding-top:16px">
    {your_name}<br>
    Web Design for Local Businesses<br>
    {your_website}
  </p>
</div>
"""


def send_email(lead: dict, screenshot_path: str, account: dict, your_website: str) -> str:
    """Sends an email immediately from the given account. Returns Gmail message ID."""
    msg = MIMEMultipart("related")
    msg["Subject"] = f"I built a free website mock for {lead['business_name']} 🏠"
    msg["From"]    = f"{account['name']} <{account['email']}>"
    msg["To"]      = lead["email"]

    msg.attach(MIMEText(build_email_body(lead, account["name"], your_website), "html"))

    with open(screenshot_path, "rb") as f:
        img = MIMEImage(f.read(), _subtype="png")
    img.add_header("Content-ID",          "<mocksite_preview>")
    img.add_header("Content-Disposition", "inline", filename="website_preview.png")
    msg.attach(img)

    raw  = base64.urlsafe_b64encode(msg.as_bytes()).decode()
    token = get_access_token_for_account(account)
    resp = requests.post(
        "https://gmail.googleapis.com/gmail/v1/users/me/messages/send",
        headers={"Authorization": f"Bearer {token}"},
        json={"raw": raw},
    )
    resp.raise_for_status()
    return resp.json()["id"]


def create_draft(lead: dict, screenshot_path: str, your_name: str, your_email: str, your_website: str) -> str:
    """Creates a Gmail draft (legacy — used for testing). Returns draft ID."""
    msg = MIMEMultipart("related")
    msg["Subject"] = f"I built a free website mock for {lead['business_name']} 🏠"
    msg["From"]    = your_email
    msg["To"]      = lead["email"]

    msg.attach(MIMEText(build_email_body(lead, your_name, your_website), "html"))

    with open(screenshot_path, "rb") as f:
        img = MIMEImage(f.read(), _subtype="png")
    img.add_header("Content-ID",          "<mocksite_preview>")
    img.add_header("Content-Disposition", "inline", filename="website_preview.png")
    msg.attach(img)

    raw  = base64.urlsafe_b64encode(msg.as_bytes()).decode()
    resp = requests.post(
        "https://gmail.googleapis.com/gmail/v1/users/me/drafts",
        headers={"Authorization": f"Bearer {get_access_token()}"},
        json={"message": {"raw": raw}},
    )
    resp.raise_for_status()
    return resp.json()["id"]


if __name__ == "__main__":
    print("Gmail draft module loaded. Run pipeline.py to use it.")
