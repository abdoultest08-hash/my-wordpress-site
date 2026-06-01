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
import os
import json
from email.mime.multipart import MIMEMultipart
from email.mime.text       import MIMEText
from email.mime.image      import MIMEImage
from google.auth.transport.requests import Request
from google.oauth2.credentials      import Credentials
from google_auth_oauthlib.flow      import InstalledAppFlow
from googleapiclient.discovery      import build

SCOPES = ["https://www.googleapis.com/auth/gmail.compose"]
CREDS_FILE = os.path.join(os.path.dirname(__file__), "credentials.json")
TOKEN_FILE  = os.path.join(os.path.dirname(__file__), "token.json")


def get_gmail_service():
    creds = None
    if os.path.exists(TOKEN_FILE):
        creds = Credentials.from_authorized_user_file(TOKEN_FILE, SCOPES)
    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            flow = InstalledAppFlow.from_client_secrets_file(CREDS_FILE, SCOPES)
            creds = flow.run_local_server(port=0)
        with open(TOKEN_FILE, "w") as f:
            f.write(creds.to_json())
    return build("gmail", "v1", credentials=creds)


def build_email_body(lead: dict, your_name: str, your_website: str) -> str:
    """Returns the HTML email body with the screenshot embedded via cid."""
    biz   = lead["business_name"]
    owner = lead.get("owner_name", "there")
    city  = lead.get("city", "your city")

    return f"""
<div style="font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#1A2533">
  <p style="font-size:15px">Hi {owner},</p>

  <p style="font-size:15px;line-height:1.7">
    I was searching for {lead.get('industry','service')} companies in {city} and came across
    <strong>{biz}</strong>. I noticed you don't have a website yet — so I went ahead and built
    a <strong>free mock homepage</strong> to show you what it could look like.
  </p>

  <p style="font-size:15px;line-height:1.7">Here's a preview:</p>

  <!-- Screenshot embedded below -->
  <div style="border-radius:10px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.15);margin:20px 0">
    <img src="cid:mocksite_preview" alt="{biz} Website Preview"
         style="width:100%;display:block;border-radius:10px">
  </div>

  <p style="font-size:15px;line-height:1.7">
    This is just a starting point — I can customize it fully with your services, photos,
    reviews, and anything else you'd like. A professional website helps you:
  </p>
  <ul style="font-size:15px;line-height:2">
    <li>✅ Show up on Google when locals search for you</li>
    <li>✅ Look more professional than competitors</li>
    <li>✅ Get more calls and enquiries automatically</li>
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


def create_draft(lead: dict, screenshot_path: str, your_name: str, your_email: str, your_website: str) -> str:
    """Creates a Gmail draft and returns the draft ID."""
    service = get_gmail_service()

    msg = MIMEMultipart("related")
    msg["Subject"] = f"I built a free website mock for {lead['business_name']} 🏠"
    msg["From"]    = your_email
    msg["To"]      = lead["email"]

    # HTML body
    html_body = build_email_body(lead, your_name, your_website)
    msg.attach(MIMEText(html_body, "html"))

    # Embed screenshot as inline image
    with open(screenshot_path, "rb") as f:
        img = MIMEImage(f.read(), _subtype="png")
    img.add_header("Content-ID",          "<mocksite_preview>")
    img.add_header("Content-Disposition", "inline", filename="website_preview.png")
    msg.attach(img)

    raw = base64.urlsafe_b64encode(msg.as_bytes()).decode()
    draft = service.users().drafts().create(
        userId="me",
        body={"message": {"raw": raw}}
    ).execute()

    return draft["id"]


if __name__ == "__main__":
    print("Gmail draft module loaded. Run pipeline.py to use it.")
