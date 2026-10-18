# WordPress Care-Sector Site Build — Handover Notes

**Purpose of this document**
Everything learned building the Winserve Care Services website from scratch, written so a fresh chat can pick up the method without repeating the mistakes. Next project is a **care agency site with job listings and agency-staff recruitment**, so a section at the end covers what will be different.

Last updated: October 2026. Reference build: Winserve theme v2.3.0.

---

## 1. How to start a new build (do this first, every time)

The single biggest time-waster on the last project was not establishing ground truth at the start. **Several sessions of work were done on the wrong theme folder** because nobody checked which theme was actually live.

Before writing a line of code, confirm:

| Question | Why it matters |
|---|---|
| What is the **exact theme folder name**? | `wp-content/themes/<name>` — work on the wrong one and everything is wasted |
| Is the theme **in git**, or does it only exist in a zip the client holds? | If it is only in a zip, the working copy dies with the container |
| Which **zip is the current source of truth**? | Ask the client to attach it. Do not assume the repo matches |
| Is this a **child theme** or standalone? | Changes the file structure entirely |
| What is the **live URL**? | For checking what is actually deployed |

Then immediately:
1. Extract the client's zip into a clean directory
2. Run `php -l` across every PHP file to confirm it is intact
3. Verify every referenced image actually exists
4. Note the theme `Version:` in `style.css`

**Keep the working copy somewhere durable.** Cloud containers get reclaimed. `/tmp` is wiped. The last project lost its entire working copy twice and had to be restored from a zip the client still had. Either commit to git or keep re-exporting zips after every milestone.

---

## 2. WordPress deployment gotchas

These cost real time. All of them are avoidable.

### 2.1 Zip structure
WordPress requires the **theme folder at the root of the zip**.

```
CORRECT                      WRONG
my-theme.zip                 my-theme.zip
└── my-theme/                └── wp-content/
    ├── style.css                └── themes/
    ├── functions.php                └── my-theme/
    └── ...
```

Wrong structure produces: *"The package could not be installed. The theme is missing the style.css stylesheet."*

Build with:
```bash
cd wp-content/themes && zip -rq /path/out.zip my-theme/
```

### 2.2 WordPress will not silently overwrite a theme
Uploading a zip whose folder name matches an installed theme triggers a comparison screen with a **"Replace current with uploaded"** button. If the client does not click it, nothing changes and the site looks untouched.

**Always bump `Version:` in `style.css` on every build.** If both sides read "2.0.0" the screen looks like nothing is new and gets dismissed.

### 2.3 Browser cache will hide your CSS changes
Hardcoded asset versions mean the browser never refetches:

```php
// BAD — version never changes, CSS cached forever
wp_enqueue_style('main', $uri.'/assets/css/main.css', [], '2.1.0');
```

Use file modification time so it changes on every upload:

```php
function theme_asset_ver( $rel ) {
    $f = get_template_directory() . $rel;
    return file_exists($f) ? (string) filemtime($f) : THEME_VERSION;
}
wp_enqueue_style('main', $uri.'/assets/css/main.css', [], theme_asset_ver('/assets/css/main.css'));
```

Set this up on day one. It permanently removes a whole class of "I uploaded it but nothing changed" confusion.

### 2.4 Add a build marker
Put a comment at the top of `footer.php`:

```html
<!-- Theme build 2.3.0 / description -->
```

The client can right-click → View Page Source and confirm in two seconds whether the upload actually landed. Saves long diagnostic conversations.

### 2.5 Upload size
A 14MB zip timed out on delivery. Keep builds **under ~6MB**. See the image section below.

### 2.6 Post content lives in the database, not the theme
Blog dates, page content and anything created through the admin are **not in the theme files**. A theme-based seed script typically skips posts that already exist, so re-running it fixes nothing. Those edits happen in **Posts → All Posts → Quick Edit**.

---

## 3. Responsive layout — what actually works

### 3.1 Breakpoints used on the last build
```
1100px   footer logo strip wraps
980px    nav spacing tightens
900px    two-column layouts stack, hero splits
860px    inline grids collapse, logo grid 5→3
768px    main mobile breakpoint — nav → hamburger, topbar hides
600px    hero buttons stack, carousel cards resize
520px    logo grid 3→2
480px    chat widget goes full screen
```

### 3.2 Kill horizontal scroll from the start
The classic "I can push the page sideways and see white" bug:

```css
html, body { overflow-x: hidden; max-width: 100%; }
* { min-width: 0; }
img, iframe, video { max-width: 100%; }
```

`min-width: 0` matters — flex and grid children default to `min-width: auto` and refuse to shrink below content width, which is what actually causes most overflow.

### 3.3 Inline grid styles do not respond to media queries
This is the one that catches everyone. Inline styles beat stylesheet rules, so:

```html
<div style="display:grid;grid-template-columns:1fr 1fr;">
```

will **never** collapse on mobile from a normal media query. Two options:

**Option A — attribute selectors (retrofit, no markup changes):**
```css
@media (max-width: 900px) {
  div[style*="grid-template-columns:1fr 1fr"],
  div[style*="grid-template-columns:1.15fr 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
```
Note `*=` is a substring match, so `"1fr 1fr"` also catches `"1fr 1fr 1fr"`.

**Option B — use classes from the start.** Better. Only reach for Option A when retrofitting someone else's markup.

### 3.4 Mobile menu must be visibly tappable
A white hamburger on a white nav is invisible. This happened because a stale rule from an earlier dark-nav design was never removed.

```css
.menu-toggle {
  background: #fff;
  border: 2px solid var(--navy);   /* visible outline */
  border-radius: 6px;
  width: 44px; height: 44px;        /* 44px = accessibility minimum */
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 4px;
}
.menu-toggle span { background: var(--navy); width: 20px; height: 2.5px; }
```

**44×44px is the minimum touch target.** Especially relevant in care, where a lot of visitors are older family members.

Also check the toggle class matches between CSS and JS. We shipped a bug where JS toggled `.nav-open` but CSS listened for `.open`.

### 3.5 Carousels on narrow screens
```css
@media (max-width: 600px) {
  .review-card { width: 78vw !important; min-width: 78vw !important; }
}
```
Viewport units rather than fixed pixels so the card always fits.

---

## 4. Images — the biggest source of both weight and bugs

### 4.1 Compression workflow
Phone photos arrive at 1–2MB each. Target ~1200px longest edge, JPEG quality 78–82.

```python
from PIL import Image
im = Image.open(src).convert('RGB')
im.thumbnail((1200, 1200), Image.LANCZOS)
im.save(dst, 'JPEG', quality=78, optimize=True)
```

Typical result: 1.2MB → 150KB with no visible difference at display size. On the last build this took the theme from 14MB to 3.6MB.

**JPEG for photos. PNG only for logos and anything needing transparency.** Clients often ask for PNG thinking it is better quality — for photographs it is just bigger.

### 4.2 `object-fit: cover` crops from the centre
Put a portrait photo in a short container and it cuts the heads off. Happened twice.

```css
object-fit: cover;
object-position: center 8%;   /* or center top */
```

**Always eyeball the result.** Work out where the faces are in the source and anchor accordingly. For a group photo with faces in the top third, `center 8%` to `center 20%` usually works.

Better still: crop the source to the right aspect ratio rather than relying on CSS to do it.

### 4.3 `width`/`height` attributes can stretch images
Adding them is good for layout stability and Core Web Vitals, but:

```css
.footer-logo { height: 48px; }   /* no width declared */
```
```html
<img class="footer-logo" width="2865" height="1047">   <!-- stretches full width -->
```

Without the attribute the browser derived width from aspect ratio. With it, the attribute drives width.

**Rule: any image whose height is set in CSS must also declare `width: auto`.** Audit for rules that set height without width before adding attributes anywhere.

### 4.4 Lazy loading
```html
<img src="..." loading="lazy" decoding="async">
```

Leave above-the-fold images (header logo, hero) **eager** or they flash in.

On an image-heavy page this was the single biggest win: an About page went from 2.7MB first-load to 420KB, roughly 85% faster, by deferring 24 images that were not in view.

**Limitation:** CSS `background-image` cannot be lazy loaded. If a page uses background images for cards, that weight always loads. Worth using real `<img>` tags where you want the deferral.

### 4.5 Logo strips
Different logos have wildly different aspect ratios. Setting a common `height` makes square ones render narrow and tall and the row looks broken.

Give every logo an identical invisible box:
```css
.logo-box { height: 62px; max-width: 140px; display: flex;
            align-items: center; justify-content: center; }
.logo-box img { max-height: 100%; max-width: 100%;
                width: auto; height: auto; object-fit: contain; }
```

Other logo lessons:
- **Even splits matter.** 10 logos as 5+5 looks balanced. 9 as 5+4 leaves an awkward short row. Add or remove one to get an even split rather than fighting it with CSS
- **Dark footers need light logo variants.** Black-text logos vanish. Generate a white version and composite it against the footer colour to check before shipping
- **Strip white backgrounds** from JPEG logos to transparent PNG so they sit on any background
- **Do not grayscale them.** It dulls brand colours clients care about. Full colour at ~0.9 opacity looks better

### 4.6 PDF certificates to web images
```bash
pdftoppm -png -r 150 input.pdf out
```
Then rotate and compress. **The rotation direction is a coin flip** — render it and look at it. We got 270° wrong on the first attempt and shipped an upside-down certificate.

### 4.7 Delete unused images
29 unused images were sitting in the theme, 3MB of files no page referenced, shipping in every upload. Scan before every release:

```python
used = set()
for f in php_and_css_files:
    used |= set(re.findall(r'images/([\w.\-]+\.(?:jpg|png|webp))', open(f).read()))
# anything in the images folder not in `used` is dead
```

---

## 5. Working with PHP templates

### 5.1 Never regex `<img>` tags in PHP templates
This breaks:
```python
re.sub(r'<img\b[^>]*>', ...)   # WRONG
```
because the tag contains PHP:
```php
<img src="<?php echo esc_url(...); ?>/img.jpg">
```
and `[^>]*` stops at the `>` inside `?>`, producing mangled output and a parse error across every template.

Write a scanner that skips `<?php ... ?>` blocks:
```python
def find_img_tags(s):
    spans = []; i = 0
    while True:
        st = s.find('<img', i)
        if st == -1: break
        j = st + 4
        while j < len(s):
            if s.startswith('<?', j):
                e = s.find('?>', j)
                j = (len(s) if e == -1 else e + 2); continue
            if s[j] == '>': spans.append((st, j+1)); break
            j += 1
        i = j + 1
    return spans
```

### 5.2 Validate after every batch edit
```bash
for f in *.php; do php -l "$f"; done
node --check assets/js/*.js
```
Catching a parse error immediately is far cheaper than finding it after the client uploads.

### 5.3 Keep a backup before bulk operations
```bash
cp -r theme theme-backup-pre-change
```
The regex incident above broke ten files at once. Restoring from backup took seconds.

### 5.4 Audit before removing anything
Before trimming Google Font weights, three were removed that were in active use — weight 500 appeared 17 times and italic 9 times. The site would have rendered wrong across every page.

```bash
grep -o "font-weight: *[0-9]*" assets/css/*.css | grep -o "[0-9]*$" | sort -n | uniq -c
```

Same discipline before deleting CSS rules, images or template parts. **Grep for usage first, always.**

---

## 6. Copy and content

### 6.1 House style that worked
- No em dashes. Commas or full stops instead
- Short sentences, active voice
- No corporate filler: "bespoke", "holistic", "person-centred journey", "we pride ourselves on", "tailored solutions"
- Specific beats vague. "Handed 3:1 packages by local authorities since 2020" lands harder than "trusted provider"
- Write for the reader's decision, not the company's ego

### 6.2 Check your numbers agree with each other
The site simultaneously claimed 70+ carers, 50+ carers, 70+ reviews, 74 reviews, 6+ years and 5 years. A commissioner doing due diligence notices.

**Before launch, grep every number and reconcile it.** Pick one figure per fact and use it everywhere, including structured data.

### 6.3 Do not over-repeat trust signals
CQC appeared six times on one homepage — badge, hero circle, card, widget, stats bar, why-choose-us. After the second mention it stops registering. Two or three placements is plenty.

### 6.4 Cut duplicated sections
The homepage had a reviews carousel and a featured testimonial showing **the same quote from the same person**, one directly below the other. Read the whole page top to bottom before adding anything new.

---

## 7. Care-sector specifics

### 7.1 Regulatory language is a real constraint
The client initially said to avoid the word "complex" entirely because they were not registered for complex care. Later they gained **TDDI registration (Treatment of Disease, Disorder or Injury)**, which changed what could be claimed.

**Check the provider's actual CQC record before writing capability claims.** It is public:
`cqc.org.uk` → search the provider name. It lists:
- Overall rating and all five domain ratings
- Registered regulated activities (Personal Care, TDDI, etc.)
- Registered Manager and Nominated Individual
- Registered address and phone

Writing a claim the registration does not support is a problem with both the regulator and with commissioners who place on the strength of it.

### 7.2 Cross-check the CQC record against the site
We found the CQC record listed a **different phone number** to the website — one digit transposed. A commissioner looking them up would have called a dead number. Also check the registered address matches.

### 7.3 Two audiences, two messages
Care providers serve commissioners and private families, and they need different copy on the same site:

| | Commissioners / case managers | Private families |
|---|---|---|
| Cares about | Capacity, compliance, speed, cost, fewer handbacks | Trust, kindness, reliability, dignity |
| Proof | CQC rating, registrations, ratios, references | Reviews, photos, staff warmth |
| CTA | "Talk to us about a placement" | "Find care" / "Free assessment" |

Build a distinct commissioner section. Most care sites are written entirely for families and leave the commercially larger audience with nothing.

### 7.4 Trust marks that matter in UK care
CQC rating · DBS checked · Real Living Wage Employer · Armed Forces Covenant · CHAS · QCS · NHS partnership · Homecare.co.uk reviews

**CQC provide a free embeddable ratings widget** — use it. Live and verifiable beats a static badge.

### 7.5 Privacy requests
The client asked to keep the director's name off the site to reduce cold calling. Honour it, but tell them plainly if the name is already public on the CQC record, so the decision is informed rather than falsely reassuring.

---

## 8. Layout patterns worth reusing

### 8.1 Split hero (desktop)
```
┌─────────────────┬─────────────────┐
│  TINTED PANEL   │                 │
│  • badge pill   │     PHOTO       │
│  • big heading  │   (full bleed)  │
│  • paragraph    │                 │
│  • CTA + phone  │                 │
└─────────────────┴─────────────────┘
```
```css
.hero-split { display: grid; grid-template-columns: 1fr 1fr; min-height: 520px; }
@media (max-width: 900px) {
  .hero-split { grid-template-columns: 1fr; }
  .hero-split-media { order: -1; min-height: 300px; }  /* image first on mobile */
}
```
Reads as more designed than a full-bleed background photo, and text legibility is guaranteed without heavy overlays.

### 8.2 Topbar with hard-right contact block
Info left, phone as a solid colour block running to the screen edge. Small detail, disproportionate effect on looking deliberate.

### 8.3 Section rhythm
```
small UPPERCASE EYEBROW (letter-spacing .18em, accent colour)
Large centred heading
optional intro paragraph, max-width ~760px
────── 56px gap ──────
content
```
Section padding 96px desktop / 64px mobile. Generous spacing is most of what separates a template that looks premium from one that looks cheap.

### 8.4 Alternate image sides down the page
Text left / image right, then image left / text right. Stops long pages feeling like a wall.

### 8.5 Break up adjacent same-colour sections
Three grey sections in a row merge into one flat expanse. Alternate grey / white / grey, or add a hairline border.

### 8.6 Full-bleed hero overlay with a bright photo
```css
background: linear-gradient(100deg,
  rgba(12,22,58,0.74) 0%,
  rgba(12,22,58,0.60) 45%,
  rgba(12,22,58,0.38) 100%);
```
Directional gradient — heavy where the text sits, light where the photo should show. Plus a subtle `text-shadow` on the heading so the overlay can stay lighter.

---

## 9. Forms and email

### 9.1 Pattern that worked
```php
// Front end
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
  <input type="hidden" name="action" value="theme_contact">
  <?php wp_nonce_field('theme_contact', 'theme_nonce'); ?>
  ...
</form>

// Handler in functions.php
add_action('admin_post_nopriv_theme_contact', 'handle_contact');
add_action('admin_post_theme_contact',        'handle_contact');
```

Always: nonce verification, `sanitize_text_field` / `sanitize_textarea_field` / `sanitize_email`, honeypot field, and a transient-based rate limit.

### 9.2 Route by enquiry type
General enquiries to `enquiries@`, job applications to `hr@`. Tag subject lines `[CARE ENQUIRY]`, `[COMMISSIONER]`, `[JOB APPLICATION]` so the inbox can be filtered.

### 9.3 `wp_mail()` alone is not enough
Default PHP mail lands in spam or silently fails. **Install WP Mail SMTP and authenticate properly** — Gmail/Google Workspace via App Password, or the host's SMTP. Do this before launch, and send a live test from every form.

### 9.4 Send an acknowledgement
Auto-reply to the enquirer confirming receipt with the phone number for anything urgent. Cheap, and it stops people submitting three times.

---

## 10. Chat widget (rule-based, no AI)

Built as a guided menu rather than an AI assistant, deliberately.

**Why rule-based for care:** the moment someone types "my mum has dementia and keeps falling", that is special-category health data under UK GDPR. Routing it through a third-party AI service means a data processing agreement, a DPIA and a privacy policy update. A rule-based widget running on the site's own server has none of that, costs nothing, and cannot hallucinate.

**Structure:** three files — PHP handler, JS flow, CSS. Button-driven branches, each collecting structured fields, posting once to `admin-ajax.php`.

**Must-haves:**
- Consent checkbox linked to the privacy policy
- "Not for emergencies — call 999, or 111 for urgent medical advice"
- Out-of-hours status based on `new Date().getHours()`
- Honeypot and rate limit on the handler
- Full-screen panel below 480px
- An "About us" branch — gives a single informative answer then returns to menu. Useful for commissioners sizing up the provider

---

## 11. Performance checklist

Run before every launch:

- [ ] Delete unused images (scan, do not guess)
- [ ] Compress to ~1200px, quality 78–82
- [ ] `loading="lazy"` on everything below the fold
- [ ] `width`/`height` on all images — and confirm no CSS height-without-width rules
- [ ] `filemtime()` asset versioning
- [ ] Preconnect to Google Fonts, preload the hero image
- [ ] Only request font weights actually used (**grep first**)
- [ ] Every page under ~1.5MB first load
- [ ] Theme zip under 6MB

**Reference figures from the last build:** homepage 1.18MB first load, About 420KB with 2MB deferred, theme 3.6MB total.

**After launch, client-side:** a caching plugin (LiteSpeed Cache or WP Super Cache) plus Cloudflare's free tier. Those two usually beat every theme-side optimisation combined, but they need the live site to configure.

---

## 12. The next project — care agency with job listings

What carries over, and what is genuinely new.

### 12.1 Reuse directly
Deployment workflow · responsive patterns · image pipeline · forms and email routing · chat widget · trust marks · two-audience thinking · performance checklist · house style

### 12.2 New build requirements

**Jobs need a custom post type, not pages.**
```php
register_post_type('job', [
  'public' => true,
  'has_archive' => true,
  'rewrite' => ['slug' => 'jobs'],
  'supports' => ['title','editor','thumbnail','excerpt'],
  'menu_icon' => 'dashicons-businessman',
]);
```
With taxonomies for **location**, **role type** (care assistant, support worker, nurse, senior) and **contract** (full time, part time, bank, agency).

Templates needed: `archive-job.php` (listing with filters), `single-job.php` (detail + apply form), `taxonomy-job_location.php`.

Critically, this lets the client add and close vacancies from the WordPress admin without touching the theme. On the last project jobs were hardcoded into a template, which meant every vacancy change was a developer job. **Do not repeat that.**

**Applications need file upload.** CV attachment means `enctype="multipart/form-data"`, `wp_handle_upload()`, MIME restriction to PDF/DOC/DOCX, a size cap, and uploads stored outside the web root or protected. Attach to the HR email or store as a custom post type for a proper applicant pipeline.

**Two distinct funnels.** "Apply for this specific role" and "Join our bank / register interest" are different journeys and need different forms. Agency staffing lives on the second one.

**Expect higher mobile share.** Care staff job-hunt on phones far more than families research care on phones. Design the job listing and application flow mobile-first rather than adapting afterwards.

**Add structured data for jobs.** `JobPosting` schema gets listings into Google Jobs, which is free candidate traffic:
```json
{"@type":"JobPosting","title":"...","datePosted":"...","hiringOrganization":{...},
 "jobLocation":{...},"baseSalary":{...},"employmentType":"FULL_TIME"}
```
Worth doing properly — it is one of the highest-return things on a recruitment site.

**Sector-specific content candidates:** pay rates and benefits, shift patterns, training and career progression, DBS and right-to-work requirements, refer-a-friend schemes, staff testimonials.

### 12.3 Ask the client up front
1. Theme folder name, current zip, is it in git
2. Who hosts the email, for SMTP setup
3. CQC registration and record — verify the claims
4. Which audiences: clients, candidates, commissioners, or all three
5. Do they want to manage vacancies themselves (almost always yes → CPT)
6. Visa sponsorship position — affects application form logic
7. Any names or details that must stay off the site

---

## 13. Process lessons

**Ask before building, not after.** A short plan approved in advance beats a large rebuild. But once the client has approved a plan, **build it** — do not stage half and announce the rest as "next". That reads as having forgotten.

**One change at a time when debugging.** Several bugs were introduced by batch operations that were individually sensible.

**Verify the artefact, not just the code.** Grep inside the built zip before sending. "I made the change" and "the change is in the file the client received" are different claims.

**Say when something is your mistake.** The wrong-theme episode, the stretched logo, the font weights. Naming it plainly and showing the fix keeps trust; quietly correcting it does not.

**Flag what you notice even when unasked.** The phone number mismatch, the director's name being public, the duplicate testimonial. Clients value the things they had not spotted.

**Push back once, then build.** If a concern is raised and the client confirms their decision, note it and proceed in full.

---

## 14. Quick reference

```bash
# Build a WordPress-installable theme zip
cd wp-content/themes && zip -rq /path/theme.zip my-theme/

# Validate everything
for f in *.php; do php -l "$f"; done
node --check assets/js/*.js

# Find images nothing references
# (grep all php/css for images/<name>, diff against the folder)

# Compress a folder of photos
python3 -c "
from PIL import Image; import os
for f in os.listdir('.'):
    if f.lower().endswith(('.jpg','.png')):
        im = Image.open(f).convert('RGB')
        im.thumbnail((1200,1200), Image.LANCZOS)
        im.save(f.rsplit('.',1)[0]+'.jpg','JPEG',quality=78,optimize=True)
"

# Check font weights in use before trimming the request
grep -o "font-weight: *[0-9]*" assets/css/*.css | grep -o "[0-9]*$" | sort -n | uniq -c

# Find rules that set height on an image with no width (stretch risk)
grep -B2 -A4 "height: *[0-9]*px" assets/css/main.css | grep -i "logo\|img"
```

**Every-build checklist**
- [ ] Bump `Version:` in `style.css`
- [ ] Update the build marker in `footer.php`
- [ ] `php -l` across all templates
- [ ] Confirm every image reference resolves
- [ ] Grep the built zip for the changes
- [ ] Tell the client to click "Replace current with uploaded" and hard refresh
