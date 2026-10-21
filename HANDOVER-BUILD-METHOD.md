# How the HCC-Temps site was built — method handover

**Purpose:** a repeatable method for building a client website from scratch, as done for HCC-Temps (October 2026). Use it to rebuild the Winserve Care website the same way. It complements `WORDPRESS-BUILD-HANDOVER.md` (the Winserve lessons file), which covers deployment, images and responsive gotchas. This file covers the **process and structure** that made the HCC result better.

Repo: `abdoultest08-hash/my-wordpress-site`, branch `claude/gallant-newton-n3wu1i`.

---

## 1. The process in seven stages

| # | Stage | Output | Why it worked |
|---|---|---|---|
| 1 | **Ground truth** on the existing site | Notes on platform, plugins, post types, content | Avoided guessing. Found the live Job Board was a `job_vacancies` post type with 85 jobs, so the new theme could reuse it |
| 2 | **References** from the client | Screenshots of 2–3 sites they like (Hirework template, Michael Page) | Gave a concrete target: "Michael Page structure, Hirework cards, logo colours" |
| 3 | **Clickable mockup** as an Artifact | `mockups/<client>/` multi-page HTML, shared by link | The client approved the look before any WordPress work. Cheap to change |
| 4 | **Feedback rounds** on the mockup | Small edits, republished to the same link | Every change was visible in minutes |
| 5 | **WordPress theme** built from the mockup | `wp-content/themes/<client>/`, zipped | Same CSS and markup, so the theme looked identical to the approved mockup |
| 6 | **Local WordPress test** before every zip | Pages, forms and emails checked in a real browser | Caught real bugs before the client saw them |
| 7 | **Client documents** | Proposal PDF, requirements checklist (Word), invoice PDF | Closed the deal and collected everything needed |

Rule that held throughout: **show, then build.** Mockup first, theme second, extras (the staff portal) only after the client saw a demo.

---

## 2. Stage 1 — ground truth on the existing site

Run these against the client's current site before designing anything:

```bash
# Platform, theme and plugins
curl -sSL -A "Mozilla/5.0" https://example.com/ -o home.html
grep -oE 'generator" content="[^"]*"|wp-content/(plugins|themes)/[a-zA-Z0-9_-]+' home.html | sort | uniq -c

# Post types and taxonomies (WordPress REST API, public)
curl -sSL https://example.com/wp-json/wp/v2/types | python3 -c "import json,sys; d=json.load(sys.stdin); [print(k, d[k].get('rest_base')) for k in d]"
curl -sSL https://example.com/wp-json/wp/v2/taxonomies | python3 -c "import json,sys; d=json.load(sys.stdin); [print(k, d[k].get('types')) for k in d]"

# Count of a post type
curl -sSI "https://example.com/wp-json/wp/v2/<rest_base>?per_page=1" | grep -i x-wp-total
```

Things found this way on HCC: Impreza theme + WPBakery, a `job_vacancies` post type with 5 taxonomies, messy duplicate terms, and broken JobPosting schema pasted as text. All became talking points with the client.

**For Winserve:** the existing theme in this repo is `wp-content/themes/mybrand` (Theme Name "Winserve Care", post type `winserve_job`). Confirm with the client which folder and zip is actually live before starting (lesson 1 of the Winserve handover).

---

## 3. Stage 3 — the mockup

### Folder structure (copy this for Winserve)

```
mockups/hcc-temps/
├── _src/
│   ├── build.py              # assembles pages from partials
│   ├── partials/
│   │   ├── header.html       # top bar + header + mobile menu
│   │   └── footer.html       # accreditation marquee + footer
│   └── pages/
│       ├── index.html        # <!--title:...|key:home--> then <main>…</main> then optional <script>
│       ├── jobs.html, job.html, register.html, clients.html, about.html, contact.html, upload.html
├── assets/
│   ├── site.css              # ALL styles, shared by every page
│   └── site.js               # shared data (jobs list) and behaviour
├── img/                      # compressed JPGs, logo PNG, accred/*.png
└── index.html, jobs.html …   # BUILT output (don't edit by hand)
```

- Edit files in `_src/`, then run `python3 _src/build.py` from the mockup folder.
- `index.html` is written **without** `<!doctype>`/`<head>` because the Artifact wraps the main page. Every other page is a full document.
- The header partial marks the current page with `data-key="..."` → `aria-current="page"`.

### Publishing the mockup as an Artifact

Publish `index.html` and pass every other file in `files`, using `root` set to the mockup folder so paths stay relative:

```
Artifact publish
  file_path: /home/user/my-wordpress-site/mockups/hcc-temps/index.html
  root:      /home/user/my-wordpress-site/mockups/hcc-temps
  files:     {"assets/site.css": "assets/site.css", "jobs.html": "jobs.html", "img/hero.jpg": "img/hero.jpg", …}
```

Republishing the same `file_path` keeps the same link, so the client's link always shows the latest version.

### Design system used (HCC)

- **Colours taken from the logo** with Pillow (most common non-white pixels), then named as CSS tokens: `--ink`, `--navy`, `--aqua`, `--aqua-deep` (darker for text contrast), `--sun` (yellow for main buttons), `--coral`, `--cocoa`, `--paper`.
- **Fonts:** Bricolage Grotesque 800 (headings) + Figtree (body), from Google Fonts.
- **Section patterns that worked:**
  - Top bar with slogan + phone (replaced a "mockup" ribbon)
  - **Expanding hero** (Michael Page style): a sticky stage inside a tall track; a framed photo grows to full screen as you scroll, a dark shade fades in so text stays readable. Respects reduced motion
  - Search band + featured cards with photos, badges (New/Urgent), "Show more" then "See all"
  - **Five steps with a live preview card** that changes per step (Hirework layout)
  - Stats band with figures marked "Confirm"
  - Blog strip, testimonial collage (Michael Page), reviews carousel
  - **FAQ with tabs** (candidates / employers)
  - CTA band with floating circular photos
  - **Accreditation marquee** (logos scroll, pause on hover) on every page
  - **White footer** with a five-colour brand stripe
- Mobile checked at 390px wide on every page. No horizontal scroll.

### Images for the mockup

- Free stock sites block downloads from the cloud session. Unsplash API needs a key, Pexels search returns 403.
- **Higgsfield on the free plan:** only the `z_image` model works (others say "Requires basic plan"). It rate-limits at about 2 jobs at once, so submit in small batches and retry the ones that fail. Some images come out with AI artefacts (two phones), so look at each one before using it.
- Compress with Pillow: hero 1600px, cards 720px, JPEG quality 72. All 12 images came to about 500KB in total.
- **For Winserve you already have real images**, so skip generation and just compress them.

### Mockup gotchas we hit

- `[hidden]` doesn't hide an element that has `display: grid` in CSS → add `.mobile-nav[hidden] { display: none; }` (the Artifact skeleton adds `[hidden]{display:none!important}`, but the WordPress theme needs its own rule).
- An `<img>` with `width`/`height` attributes **and** CSS `aspect-ratio` needs `height: auto`, or the attribute height wins.
- **Class-name collisions** across sections: `.role` (register cards vs review text) and `.review` (homepage section padding vs staff document buttons) both broke layouts. Prefix component classes (`.role-pick`, `.doc-review`).
- `loading="lazy"` images don't appear in full-page screenshots; scroll the page in the test first.

---

## 4. Stage 5 — turning the mockup into a WordPress theme

### Theme structure

```
wp-content/themes/hcc-temps/
├── style.css                 # theme header only (bump Version on every build)
├── functions.php             # constants + require inc/*
├── inc/
│   ├── setup.php             # supports, menus, enqueue (filemtime versions), Customizer options, helpers
│   ├── jobs.php              # post type + taxonomies (only if not already registered), job meta box, filters, cards, JobPosting schema
│   ├── applicants.php        # private applicant records, document checklists, private folder, admin screens
│   ├── forms.php             # apply / documents / contact / cover handlers + branded emails
│   ├── seed.php              # demo content on activation + Tools → "demo content" remover
│   └── portal.php            # candidate + staff portals (HCC extra)
├── header.php, footer.php, front-page.php, index.php, page.php, single.php, 404.php
├── archive-job_vacancies.php, single-job_vacancies.php, taxonomy.php
├── templates/                # page templates: register, clients, about, contact, upload, portal-login, portal, staff
├── template-parts/           # apply-form.php, faq.php
├── assets/css/main.css       # = mockup site.css + WordPress additions
├── assets/js/main.js         # = mockup behaviour, each block guarded by "if element exists"
├── assets/img/               # same images as the mockup
└── screenshot.png            # 1200×900, shown in Appearance → Themes
```

### Patterns worth reusing

- **Customizer panel** (Appearance → Customize → HCC-Temps) for phone, emails, slogan, hero text and image, homepage figures and social links. `hcc_opt('key')` reads any of them with a default. The client edits these without code.
- **Page templates assigned by meta**, plus `hcc_page_url('register')`, which finds the page by template so links survive slug changes.
- **Seeding on activation** (`after_switch_theme`): creates the pages with the right templates, sample jobs and posts, all tagged `_hcc_demo`. A **Tools → demo content** page removes them before launch.
- For features added **after** the client already activated the theme, seed with a version check on `init` (`hcc_portal_version` option), because `after_switch_theme` won't fire again.
- **Reuse the existing post type.** Register `job_vacancies` and the taxonomies only if `post_type_exists()` / `taxonomy_exists()` say no, so the live site's 85 jobs carry straight over.
- **Expired jobs hide themselves** through a closing-date meta query in `pre_get_posts`.
- **Job Board filters** are a plain GET form (`?kw=&where=&sector[]=&sort=`) handled in `pre_get_posts`; the JS just auto-submits when a checkbox changes.
- **Branded emails:** one `hcc_mail()` wrapper (HTML, logo, colour stripe), subjects tagged `[JOB APPLICATION]`, `[COVER REQUEST]`, `[DOCUMENTS]` so inboxes can filter.
- **Forms:** every form posts to `admin-post.php` with a nonce, a hidden honeypot field, a per-visitor rate limit and a redirect back with `?hcc_ok=` or `?hcc_err=`. `hcc_notice()` maps error codes to friendly messages.
- **Private files:** `uploads/hcc-private/<REF>-<random12>/` with `.htaccess` deny and `index.php`; the random suffix protects servers that ignore `.htaccess`. Files are only served through `admin-post.php?action=hcc_file` after a capability and nonce check.
- **Renamed uploads:** `REF_Surname-First_DocType_YYYY-MM-DD.ext`.

### Build and ship

```bash
# lint everything
cd wp-content/themes/hcc-temps && for f in $(find . -name "*.php"); do php -l $f | grep -v "No syntax"; done; node --check assets/js/main.js

# bump version in style.css AND functions.php (HCC_VERSION), then zip with the folder at the root
cd /home/user/my-wordpress-site && (cd wp-content/themes && zip -rq ../../dist/hcc-temps-1.2.0.zip hcc-temps)
unzip -p dist/hcc-temps-1.2.0.zip hcc-temps/style.css | grep Version
```

The client uploads under Appearance → Themes → Add New → Upload Theme and clicks **"Replace current with uploaded"**. The footer prints `<!-- Theme build x.y.z -->` so you can check the right version is live with View Source.

---

## 5. Stage 6 — testing in a real local WordPress (the biggest quality win)

Set up once per session, all in the scratchpad:

```bash
S=<scratchpad>; mkdir -p $S/wpt && cd $S/wpt
curl -sSL -o wp.zip https://wordpress.org/latest.zip && unzip -q wp.zip
curl -sSL -o sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip && unzip -q sqlite.zip -d wordpress/wp-content/plugins/
curl -sSL -o wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
printf '#!/bin/sh\nexec php %s/wp-cli.phar --allow-root --path=%s/wordpress "$@"\n' "$PWD" "$PWD" > wp && chmod +x wp
cd wordpress && cp wp-content/plugins/sqlite-database-integration/db.copy wp-content/db.php
sed -i "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$PWD/wp-content/plugins/sqlite-database-integration#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" wp-content/db.php
cp wp-config-sample.php wp-config.php
../wp core install --url=http://localhost:8099 --title=Demo --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
../wp option update siteurl http://localhost:8099 && ../wp option update home http://localhost:8099   # install guesses /wordpress — fix it
../wp rewrite structure '/%postname%/'
```

- **Router** (`router.php`) so pretty links work with `php -S localhost:8099 ../router.php` (serve real files, otherwise load `index.php`). Restart it if the session sleeps.
- **Symlink the theme** into `wordpress/wp-content/themes/` and `wp theme activate <theme>`.
- **Capture emails** with a must-use plugin that hooks `pre_wp_mail`, appends to `mail.log` and returns true. Read subjects, links and 2FA codes from that log.
- **Turn on** `WP_DEBUG` + `WP_DEBUG_LOG` (display off) and check `debug.log` is empty after every test.
- **Playwright** (`/opt/node22/lib/node_modules/playwright`, Chromium preinstalled) for end-to-end scripts: fill and submit every form, follow emailed links, log in with the emailed code, upload files, take desktop (1440) and mobile (390) screenshots, check `scrollWidth` for overflow. Stitch screenshots into one contact sheet with Pillow to review many pages at once.

Bugs this caught before the client saw them: wrong site URL, an early-translation notice, the class collisions above, the review image not showing, "Blog" highlighted on the homepage, Jetpack hiding the 2FA code box.

---

## 6. Hosting notes

- **WordPress.com hosting** (`*.wpcomstaging.com`): Jetpack's "Log in with WordPress.com" replaces the login form and **hides the Two Factor code box**. The theme switches the `sso` module off (`option_jetpack_active_modules` filter) and forces the form fields visible. Plugins need a Business plan or above.
- **Email:** install **WP Mail SMTP** on normal hosts before testing forms. WordPress.com sends mail itself.
- **Two Factor plugin** (free, by WordPress contributors): the theme forces the email-code method for portal users via `two_factor_enabled_providers_for_user`. Its code form auto-submits when the code is complete.
- **Backups:** recommend UpdraftPlus to the client's OneDrive or Google Drive, especially once ID documents live on the site.

---

## 7. Stage 7 — client documents

| Document | How it was made | File |
|---|---|---|
| Proposal | HTML with print CSS → PDF via Playwright `page.pdf({format:'A4', preferCSSPageSize:true})`. Each A4 page is an `<article class="sheet">` with `break-after: page`. Tighten `@media print` sizes until the page count is right | `proposals/hcc-temps-proposal.html` + PDF |
| Workflow diagram | CSS grid "swimlanes" (Candidate / Automatic / Team) × steps, not SVG, so it prints cleanly and stacks on phones | inside the proposal |
| Requirements checklist | Word file built with the `docx` npm library: shaded answer cells, ☐ tick boxes, tables to type into, WeTransfer instructions, "no passwords in this document" | `client-docs/HCC-Temps-Requirements-Checklist.docx` |
| Invoice | Plain HTML → PDF. **Kept out of git** because it has bank details | scratchpad only |

Final agreed deal for HCC: **£1,700, 50% upfront (£850, invoice HCC-INV1) and 50% on completion.** The next invoice is HCC-INV2 for £850, showing the deposit as paid.

---

## 8. Applying this to Winserve

**What to bring to the new session:**
1. This file and `WORDPRESS-BUILD-HANDOVER.md`.
2. 2–3 example websites you like (screenshots or links, plus a screen recording if there's an animation you want).
3. The Winserve logo and all images (originals).
4. The current Winserve content: pages, services, reviews, CQC details, contact details, accreditations.
5. Confirmation of the live theme folder and zip (`mybrand` in this repo, Theme Name "Winserve Care").

**Suggested plan:**
1. Start a new branch from this one.
2. Copy `mockups/hcc-temps/` → `mockups/winserve/`. Replace the palette with colours from the Winserve logo, swap the partials (top bar, menu, footer) and rewrite the pages for a **care provider**: home, services, commissioners, families, careers/jobs, reviews, about, contact. Keep `build.py` as it is.
3. Publish the mockup as an Artifact, go through feedback rounds, get approval.
4. Build the theme by copying the HCC theme structure, renaming the prefix (`hcc_` → `ws_`, text domain, constants) and reusing the existing `winserve_job` post type the same way HCC reused `job_vacancies`.
5. Test locally with the setup in section 5 before every zip.
6. Bump the version, zip with the folder at the root, and have the client "Replace current with uploaded".

**Differences to plan for (from the Winserve handover):** two audiences (commissioners and private families) need separate sections and calls to action; CQC rating widget and regulatory wording must match the CQC record; the rule-based chat widget; the existing job listings were hardcoded, so move them into the post type.

**Starter prompt for the new session:**

> Read `HANDOVER-BUILD-METHOD.md` and `WORDPRESS-BUILD-HANDOVER.md`. We're rebuilding the Winserve Care website using the same method as HCC-Temps: ground truth on the live site, clickable multi-page mockup as an Artifact (copy the `mockups/hcc-temps` structure), feedback rounds, then a WordPress theme tested in a local WordPress before zipping. Here are the example sites and images: …
