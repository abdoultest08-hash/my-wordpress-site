# HCC-Temps theme 2.0: install and test guide

## 1. Install

1. **Plugins → Add New**: install and activate **Two Factor** (free, by WordPress.org contributors). It sends the 6-digit login code. wp-admin shows a red notice until it is active.
2. **Appearance → Themes → Add New → Upload Theme**: upload `hcc-temps-2.0.0.zip` and choose **Replace current with uploaded**.
3. **Settings → Permalinks**: press **Save** once.
4. **Appearance → Customize → Contact details**: set **Staffing inbox (email_jobs)**. All registration, upload, cover and contact emails go there.
5. Optional: **Customize → Security**: add Cloudflare Turnstile site and secret keys. Without them the forms show a simple "I'm not a robot" tick box.

These pages are created or updated for you: Registration, Candidate Login, My Application, Staff Portal, Privacy Policy, Terms and Conditions. The old "Upload Documents" page moves to the bin.

## 2. Make yourself a staff login

- In **Users → Add New**, create a user with the role **HCC Manager** (sees bank details in full) or **HCC Staff** (bank details masked).
- Or, once you're a manager, use **Staff portal → Team → Add staff**. The new person gets an email to set their password.
- Log in at **Log in → Staff login**. After your password you get a 6-digit code by email.

## 3. Test as a candidate (use a second email address)

1. **For candidates** → tap **Registered Nurse** → **Start registration**.
2. Fill in your details, accept the terms and privacy policy, tick the robot box.
3. Enter the 6-digit code from your email.
4. Confirm the role, or change it.
5. Answer the pre-screening questions. Bank details are required.
6. Upload a few documents (phone photos are made smaller automatically). Tick **I don't have this yet** on others.
7. Do the same for training, then press **Submit my registration**.

You should then see:

- **Candidate:** a "We have your registration" email.
- **Staffing inbox:** `[REGISTRATION] HCC-00xxxx Name, Role: N items missing`. It lists every answer line by line, the referees and bank details, Submitted / Not submitted for each document, and the missing list at the end. Only the CV is attached.
- **Later uploads from the portal** send one `[DOCUMENTS]` email per batch. It goes 10 minutes after the last upload, or straight away when the candidate presses **I've finished uploading**.

## 4. Test as staff

Open the candidate from **Staff portal → Dashboard** and try:

- **✓** to approve a document.
- **⋯ → Ask to replace** to send it back for replacement.
- **⋯ → Note**: untick "Internal" to send the note to the candidate.
- Tick several items, then **Request as missing**.
- **Update for [name]** collects these changes. Nothing is emailed until you press **Preview and send email**. Unsent updates show on the dashboard under **Updates not sent**.
- **Checks**: right to work (with visa expiry), DBS, NMC and identity.
- **References**: sent and received dates, outcome, replace or add a referee.
- **Notes**: internal or a message to the candidate.
- **Declaration**: sent and signed ticks.
- **Complete profile (100%)**: overrides each item one at a time. Each needs a reason and a note.
- **Finish onboarding** works only when the profile is at 100%, the declaration is sent and signed, and there are no unsent updates. The candidate then moves to **Staff completed**.
- **Withdrawn / not qualified** closes the application; **Reopen** brings it back.
- **Download all (zip)**, **Candidate report** and **Export to Excel (CSV)**.
- Every action appears in the **Activity log**.

## 5. Import jobs

**Job Board → Import jobs (CSV)**:

1. Download the template.
2. Fill one job per row. Use the same `ref` to update a job, and `status = expired` to close one.
3. Upload, check the preview, then press **Import**.

## 6. Good to know

- Uploaded files are kept in `wp-content/uploads/hcc-private/`, named like `01 - Alex Morgan - CV.pdf`. That folder is blocked from the web, and files open only through the Staff portal, which logs every view and download.
- Team members on **About us**: **wp-admin → Team** (name, role, photo, order).
- The Privacy Policy and Terms pages are drafts: items in [brackets] need confirming before going live.
