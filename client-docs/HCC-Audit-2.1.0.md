# HCC-Temps theme audit (version 2.1.0)

A full review of the code, then automated browser tests on desktop and phone sizes, run against a local copy of WordPress set up to behave like WordPress.com:

- a header that resets page data, like the WordPress.com admin bar
- no page cache for logged-in users
- the Two Factor email code
- emails captured to a file so every message could be checked

## What was found and fixed

| # | Problem | How serious | Fixed in |
|---|---|---|---|
| 1 | The login robot check was only enforced by the browser. A bot posting straight to the login address could skip it, because WordPress's own password check overrode it. The email code still protected every account. | Security | 2.1.0 |
| 2 | Posting a made-up Jetpack "sum" field also skipped the robot check. | Security | 2.1.0 |
| 3 | Non-manager staff could see full bank details through **Candidate report** and the zip's summary page. | Privacy | 2.1.0 |
| 4 | Excel export: text typed by a candidate starting with "=" could run as a formula in Excel. | Security | 2.1.0 |
| 5 | Candidate portal and **View as candidate** loaded an empty record on WordPress.com (no name, every answer "—"). | Broken | 2.0.7 |
| 6 | Double taps could create an account and then show "already registered", or send the staff summary twice. | Bug | 2.1.0 |
| 7 | "Immunisation record (where applicable)" and optional training counted as required. Nurses could never reach 100%, and Admin-role candidates never at all. | Bug | 2.1.0 |
| 8 | Bulk **Approve** on items with nothing uploaded marked them approved with no note, getting round the override rule. Now only uploaded items are approved; the rest need an override with a note. | Bug | 2.1.0 |
| 9 | No way to undo **Finish onboarding**. Added **Move back to in progress** (it asks you to confirm, and it's recorded in the activity log). | Missing | 2.1.0 |
| 10 | **Open in Staff portal** in staff emails always landed on the dashboard after logging in. It now opens that candidate. | Usability | 2.1.0 |
| 11 | "Too many attempts" after 8 registration tries in an hour from one connection; several candidates on the same wifi could be blocked. Raised to 25. | Usability | 2.1.0 |
| 12 | Registering with an email that's already used gave no way forward. Added **Log in** and **Forgotten your password?** links. | Usability | 2.1.0 |
| 13 | Closed (withdrawn / not qualified) candidates could still upload. Their portal is now read-only. | Bug | 2.1.0 |
| 14 | "Not needed" appeared twice in View as candidate. | Display | 2.1.0 |
| 15 | Files over 10MB showed "check your connection". It now says the file is too large, before uploading. | Usability | 2.1.0 |
| 16 | The ⋯ menu on a document ran off the edge of a phone screen. It now uses the full row width, and the button says **Ask candidate to replace**. | Display | 2.1.0 |
| 17 | Earlier in testing: blank Privacy, Terms and job pages; codes rejected or sent over and over; the extra "Prove your humanity" page; notes going to staff only by mistake. | Broken | 2.0.1–2.0.6 |

## What was tested and passes

**Registration**
- **Double taps:** one account and one staff email.
- **Email codes:** a wrong code 6 times locks it; a new code then works; an old code is refused; an already-confirmed email never asks again.
- **Pre-screening:** answers are kept when bank details are missing; a 7-digit account number is refused.
- **Uploads:** HTML disguised as a PDF, empty and over-10MB files are rejected; replacements are named "(v2)"; names with apostrophes and accents work.
- **Different answers:** student visa note, British vs visa, each DBS answer.

**Logins**
- **Robot check:** posting straight to the login address, or adding a fake Jetpack field, can't skip it.
- **Wrong password:** shows a message on the branded login page.
- **Password reset:** works end to end, then you can log in with the new password and the email code.
- **Staff:** a new staff invite lets them set a password and log in; a staff email link returns to that candidate.

**Access**
- **Candidates are kept out** of the Staff portal and wp-admin, can't open another candidate's file, and can't use View as candidate.
- **Logged-out visitors** can't open any file.

**Staff portal**
- **Reviewing:** approve, ask to replace, request as missing, bulk rules, override one item at a time with a note (optional items aren't asked).
- **The update tray:** sending it, and both kinds of note showing in the candidate's portal and the **Sent to candidate** history.
- **Records:** checks, references, declaration.
- **Finishing:** Finish onboarding, then **Move back to in progress**; close and reopen.
- **Bank details:** staff see the last 4 digits everywhere (folder, report, zip); managers see them in full.
- **Export:** the Excel export is safe.

**Emails:** code, welcome, received, staff summary with the CV only, the upload email by scheduled task, update tray, ready to work, cover request and contact copy.

**Pages:** every public page at desktop and phone width loads with no errors, broken links or sideways scrolling.

## Please check on your live site

These depend on WordPress.com itself, which I can't reach with automated tests (the site blocks automated browsers).

1. **Clear the cache once after uploading.** WordPress.com dashboard → Settings → Caching (or Hosting → Server settings) → **Clear all caches**.
2. **Timezone.** WordPress.com → Settings → General → Timezone: **London**. Dates in emails and logs follow this.
3. **Password reset email.** Use "Forgotten your password?" with a test candidate and make sure the email arrives (check spam).
4. **The upload email.** After a candidate uploads in the portal, the staffing inbox should get the `[DOCUMENTS]` email within about 10 to 15 minutes, or straight away if they press "I've finished uploading".
5. **Jetpack's sum.** If Jetpack ever asks "Prove your humanity" on login, it should be inside the login box, followed by the email code. There should be no second page.

## Known limits (not bugs)

- **Private files on WordPress.com** are protected by a random, unguessable folder name and by the Staff portal checks. WordPress.com's server ignores the extra block file that other hosts use. Files are never linked publicly, and every view and download is logged.
- **If two staff change the same candidate at the same moment,** the later save wins for that one record. That's rare with a small team.
- **iPhone HEIC photos** are accepted. Some browsers can't preview them, so staff may need to press **Download**. Most iPhones convert to JPG automatically when uploading.
- **Restarting a test registration:** Candidates → Trash → Trash tab → **Delete permanently**. Moving it to the bin alone keeps the login, so the email stays "already registered".
