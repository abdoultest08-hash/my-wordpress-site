# HCC-Temps: checklist answers (part 1 of more to come)

Notes only. Nothing built from these yet; the client is still sending answers.

## Company details (footer, contact page, schema)
- Legal name: **HCC-Temp Staffing Ltd** (confirm exact spelling: "HCC-Temp" or "HCC-Temps")
- Company number: **11486985**
- Registered / main office: **Crown House, 94 Armley Road, Armley, Leeds, LS12 2EJ**
- Second office: London (60 Portland Road, London SE25 4PQ, from invoice; confirm it should be shown)
- Main phone: **0330 321 3124**, also the out-of-hours number
- "Leeds phone number" mentioned; no separate number given yet (confirm whether 0330 321 3124 is it)
- Opening hours: **24 hours, 7 days** (line always open)
- Coverage: **nationwide, everywhere in the UK**. Offices in Leeds and London

## Brand and copy
- Slogan: **"Temp made easy"**
- Short description: take from the current website
- Hero: **remove the dots/bubbles** (confetti) from the main hero image
- Story: started mainly to help the care industry and help staff. Operates 24 hours, helping with staffing and people in general
- USPs:
  - reliability
  - supply of candidates
  - vetted staff
  - work with vulnerable people
  - care about candidates and clients
  - cover as soon as possible
  - "bring back to the logo" (meaning unclear; ask)
- **Headline USP (bold): 2 hours to cover an emergency call, nationwide**
- Compliance turnaround: **3 days from registration to ready to start**
- Stat: **1,000+ temps covered** (confirm wording: shifts covered or temps on the books?)

## Meet the team (About us), in this order
1. David: Founder / Senior Business Development Manager
2. Geraldine: Compliance Manager
3. Arjun: Consultant
4. Debbie: Compliance Admin
5. Temi: Compliance Admin
6. Ashanti: Onboarding Manager
7. Eddie: Client Manager

That's 7 people, not 4, so the team section needs 7 cards. Photos and bios still to come.

## Accreditations
- Keep / add: **Disability Confident**, **Crown Commercial Service supplier**
- **Remove: REC Corporate Member**

## Reviews
- Use the client's Trustpilot reviews as shown on the current website

## Jobs and pay
- Job vacancies: **use them all** from the current website (job importer)
- Pay rates: use those shown on the current website
- Featured roles:
  - **Nurses, from £25 per hour+**
  - **Care Assistants**
  - **Chefs**

## Current onboarding process (as described)
1. Candidate is sent a link to the website.
2. They register with username, email, password (and repeat), first and last name. They accept the T&Cs, tick the privacy policy and complete "I'm not a robot".
3. A link goes to their email, which brings them back to the website.
4. They tick the vacancies and pick the role they want (for example nurse).
5. They upload documents, which are sent to the staffing email.
   - Staff pick up the email with the images and documents.
   - File format rules apply, and images must be clear.
6. Staff create a candidate record on **Trello**.
7. On registering, the candidate is emailed documents to sign, then a questionnaire to complete.
8. The candidate signs a **declaration document**, which is sent out manually.

### Points to think about later (not decided)
- The portal could replace the Trello card and the email inbox, since the staff portal already tracks stages.
- The documents to sign, the questionnaire and the declaration could become portal steps, for example "read and tick to sign" or a form.
- Their flow has self-registration first; ours currently has apply with CV, then invite. Decide which to keep.
- Still waiting on the role-by-role document list (for example nurse NMC PIN).

---

# Part 2 (received 23 Oct)

## Order of work agreed
1. Finish the website preview (artifact) until the client is happy with the look, then the theme.
2. Candidate onboarding (self-registration flow).
3. Mass job import.

## Team photos (illustrated portraits in Archive.zip), matched by description
| Person | Role | Description | File |
|---|---|---|---|
| David | Founder / Senior Business Development Manager | blue shirt | Confident Professional Portrait Illustration.PNG |
| Geraldine | Compliance Manager | white suit | Polished Editorial Portrait of a Black Woman.PNG |
| Arjun | Consultant | brown quarter-zip | Polished Corporate Portrait Illustration.PNG |
| Debbie | Compliance Admin | long hair, half in front | Polished Portrait with Soft Arc Background.PNG |
| Temi | Compliance Admin | glasses | Polished Corporate Portrait Illustration-2.PNG |
| Ashanti | Onboarding Manager | black blazer | Confident Black Woman in Blazer.PNG |
| Eddie | Client Manager | blue suit, bald | Polished Vector Portrait of a Bearded Professional.PNG |

First names only on the site. Sizes differ (one portrait, one landscape), so crop all to the same square.

## Onboarding requirements
- **DBS (all roles): enhanced DBS**
  - Ask: "Do you have a DBS?" Options: Yes, on the Update Service / Yes, not on the Update Service / No
  - Update Service: collect the DBS certificate number so staff can check online
- **Chef**
  - Food Hygiene Level 2 or Level 3 certificate, must be in date
  - Level 3 Allergen certificate
  - First Aid certificate
  - All must be actual certificates showing full name
- Nurse: NMC PIN (from earlier). Full role list still to come.

## Website changes
- Hero:
  - remove the coloured bubbles
  - change the care assistant image to a **registered nurse** (top priority)
  - secondary **chef** image layered alongside or from underneath
- Homepage: nurse is the main USP and the role they look for most
- Chefs: promote with **embedded videos of their chefs cooking** (videos to come)
- Key selling points:
  - **Clients:** emergency cover within **2 hours**, nationwide
  - **Candidates:** registration to ready to work in **3 days**
- Stat: cover provided figure with a slot-machine style roll when scrolled into view (1,000+ or 10,000+? confirm)
- Homepage order: **Why choose HCC above the Blog**
- Top navigation: remove Blog, or move it to just before Contact
- About us:
  - short story: committed to helping, which is why the company was created
  - supports healthcare and hospitality companies; continuity of care
  - keep it short
  - add the team with first names and roles
- Candidates page (rebuild later with onboarding):
  - explain how registration works (no CV-first approval any more; candidates create an account directly)
  - per-role sections (for example Nurse) that open to show what's needed, with a "Start registration" button

---

# Part 3 (received 24 Oct): requirements file and onboarding flow

- Role requirements: `client-docs/HCC-Mandatory-Documents.pdf`, covering 10 roles: Registered Nurse, HCA, Senior Carer, Chef, Head Chef, Kitchen Assistant, Domestic Assistant, Catering Assistant, Administrative Assistant, Barista.
- The same list is in the mockup (`assets/site.js`, `REQS`). It feeds the job pages and the Candidates page.
- Every role needs: CV, passport or driving licence, right-to-work share code and supporting documents, 2 x proof of address (last 3 months), proof of NI, professional photo, enhanced DBS on the Update Service, two references.
- Nurse extra: immunisation record, NMC PIN, NMC Statement of Entry, nursing diploma or degree.
- Training differs by role (see the PDF).

## Their current pre-screening form (screenshots, current website)
- **Account:** username, email, password and repeat, first and last name, T&Cs, privacy policy, reCAPTCHA
- **Personal details:** title, first name, surname, email, ... postcode
- **Professional referees:** two referees, each with full name, position, email, phone, address and postcode
- **Education and employment:** education level, current employment status, years' experience in the role, "Do you have a blue tunic?"
- **Questions:**
  - comfortable fit (uniform size)
  - own vehicle
  - valid DBS
  - valid training certificates (dated within the last year)
  - British citizen / UK national
  - visa type
  - right-to-work share code
- **Bank details:** account name, personal or LTD, account number ...
- **Emergency contact:** next of kin name, relationship, phone, address, postcode
- Submit Now

## Agreed onboarding flow (to design next)
1. **Register:** basic questions (first name, surname, email, phone, address), T&Cs, privacy policy, robot check. Then create the account and confirm with an email code (2FA).
2. **Confirm role:** remember the role picked on the Candidates page, ask them to confirm it, and allow switching.
3. **Phase 1, pre-screening (no uploads):** everything from their form.
   - **DBS:** No / Yes, but not on the Update Service / Yes, on the Update Service. If on the Update Service, show a heads-up: "next step we ask for your certificate".
   - **Right to work:** British citizen? Driving licence? If not British, which visa type (list the main ones).
   - Submit, which saves everything.
4. **Phase 2, documents:** upload what's requested (passport and so on).
5. **Phase 3, training certificates:** the list for their role.
6. **Never block:** candidates can tick "I don't have this yet" and still continue.
7. **Thank-you page:** "the team will review", then go to the candidate portal.
   - **One side:** all the data they submitted.
   - **Other side:** the files they uploaded, with replace buttons.
   - **Training:** an expandable folder so it doesn't clutter the dashboard.
8. **Staff:**
   - see progress
   - approve documents or add notes (as built before)
   - **multi-tick missing items** and send one automatic email: "we are missing these items from you"
9. Also to define:
   - the emails the staffing inbox receives
   - the emails candidates receive at each step

## Decisions (24 Oct)
1. **No approval gate:** anyone can register and go straight through. Unsuitable candidates are contacted manually.
2. **Staffing inbox gets 2 emails only:**
   - registration submitted (summary of answers and a list of files)
   - later uploads (grouped: "X has uploaded 3 documents, log in and check")

   **No attachments**, because of the GDPR risk and email size limits. Files stay in the Staff portal. Optional: attach the CV only.
3. **Bank details are required** in pre-screening and saved on the candidate's staff profile. Managers see them in full; others see the last 4 digits.
4. **Declaration** is sent manually by admin after all documents are approved, then recorded with "Declaration sent" and "Signed received" ticks.
5. **Admin tools:**
   - "Approve all uploaded"
   - "Mark complete (override)" with a required reason, logged with the staff member's name
   - "Complete profile (100%)": approves all uploaded files, then overrides the rest with a reason

## Decisions (24 Oct, round 2)
- **Staff email 1, registration submitted:**
  - every detail line by line: name, surname, contact, right to work, DBS, experience, **referees in full**, emergency contact, **bank details**
  - every document and certificate as Submitted / Not submitted
  - **"Missing, to be submitted" summary at the end**
  - **the CV is attached**; everything else is checked in the portal
- **Staff email 2:** sent every time a candidate uploads or replaces files after submitting. One email per upload, listing everything in it.
- **Candidate emails from staff are batched:** approvals, replace requests, missing items and notes go into an **"Update for candidate" tray**, and staff press Send for one combined email. Suggestion: unsent updates go automatically at 6pm.
- **Notes:** staff can add a note on any document, internal or for the candidate. Candidate notes join the tray.
- **Overrides:** a reason and a **required note for each item**, done one at a time when several items are ticked.
- **Activity log** records everything, with staff name and time.
- **Logins:** staff and candidates enter an email code every time, plus a robot check (Cloudflare Turnstile).

## Decisions (24 Oct, round 3)
- **No automatic sending.** Unsent candidate updates show on the staff **Dashboard → "Updates not sent"**: candidate, number of changes, who made them, how long they've waited (red after 1 day), and an "Open and send" button.
- **Notes on the staff profile:**
  - **Internal compliance note:** note, signed off by (name), next action, due date. Name and next action are optional.
  - **Message to candidate:** goes into the next update email.

  Both are saved on the profile and in the activity log.
- **View vs download:** View opens in the browser (PDF viewer or image preview) without saving a copy. Download forces a copy. Both are logged for audit. Word files can't preview in the browser, so ask for PDFs where possible.
- **File names:** `01 - Alex Morgan - CV.pdf`, `02 - Alex Morgan - Passport or driving licence.pdf` ... training `T01 - Alex Morgan - Basic Life Support.pdf`. Replacements are `(v2)`.

  Zip structure: `HCC-001234 Alex Morgan - Registered Nurse/`, with a `Training/` subfolder and a summary PDF.
- **Finish onboarding:** enabled only when the profile is 100%, the declaration is sent and signed, and no updates are unsent. It asks for a sign-off name, then moves the candidate to **Dashboard → Staff completed** with application start date, sign-off date, days taken and who signed off.

## Decisions (24 Oct, round 4)
- **Checks record** on the staff profile: right to work, DBS (Update Service), NMC register (nurses), identity.
  - Each records date checked, checked by, result (Pass / Fail / Follow-up needed) and outcome notes, with a link to the official check.
  - **Visa expiry date is entered by hand** for non-British candidates.
  - Saved in the activity log.
- **References stay manual.** For each referee record: date sent, date received, outcome (Satisfactory / Concerns / Not received).
  - **Replace referee** lets staff enter new details by hand after speaking to the candidate. The old referee is kept in the history.
  - "Add another referee" is available too.
- **Duplicate warning** matches on NI number or phone number only, not name.
  - It shows on the profile, with links to the matching records, and as a "possible duplicate" tag on the dashboard.
  - Staff can mark it "Checked: not a duplicate", which is logged.
- **Withdrawn / not qualified:** close the application with an outcome (Withdrawn by candidate / Not qualified / Not suitable), a reason, a sign-off name and a date.
  - The candidate moves to the **Dashboard → Withdrawn / not qualified** tab, with start date, closed date, outcome and who signed it off.
  - Staff can reopen it.
- Not now: expiry tracking after onboarding.

## Decisions (24 Oct, round 5)
- **Staff folder:** every section collapses (documents, training, checks, references, answers, declaration, bank, notes, activity log), with "Expand all / Collapse all". The update tray always stays open.
- **View shows the real file:** images inline, PDFs in the browser viewer. In the mockup this works for files up to 3MB uploaded in the same browser tab.
- **Request cover form:**
  - staffing inbox gets "[COVER REQUEST] {Company} is looking for {n} {role}" with a summary table (role, number, first shift, shift details, postcode, contact)
  - the client gets an automatic confirmation
- **Contact form:** goes to the staffing inbox. The sender gets a copy if they tick "Email me a copy" (ticked by default).
- **Accreditation banner:** Crown Commercial Service removed. It keeps scrolling on hover. It's still on Home, About and Contact only.
