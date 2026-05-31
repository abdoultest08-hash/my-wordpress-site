# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Repo Is

A custom WordPress theme (`mybrand`) for **Winserve Care Services** — a UK home care provider. The repo contains only `wp-content/` (no WordPress core) plus a `preview.html` static mockup.

Active theme: `wp-content/themes/mybrand/`

## Theme Architecture

### Entry Points
- `functions.php` — bootstraps everything: theme supports, menus, widget areas, asset enqueuing, assessment form handler, and requires `inc/customizer.php` + `inc/jobs.php`
- `front-page.php` — homepage, composes template parts
- Page-specific templates: `page-service.php`, `page-reviews.php`, `page-commissioners.php`

### Template Parts (`template-parts/`)
Reusable sections pulled into page templates via `get_template_part()`. Each corresponds to a homepage/page section: `hero`, `about`, `services`, `how-it-works`, `stats`, `portfolio`, `reviews`, `commissioners`, `assessment-cta`, `contact`, `card-post`.

Use the `mybrand_section( $id, $class, $callback )` helper (defined in `functions.php`) to wrap sections in `<section id="…" class="section …"><div class="container">…</div></section>`.

### Job Board (`inc/jobs.php`)
Custom post type `winserve_job` (slug: `/vacancies/`), with three taxonomies: `job_location`, `job_department`, `job_contract`. Post meta keys: `_job_salary`, `_job_ref`, `_job_urgent`, `_job_requirements`. Templates: `single-winserve_job.php`, `archive-winserve_job.php`. Job-specific assets (`assets/css/jobs.css`, `assets/js/jobs.js`) are conditionally enqueued only on job pages.

Application form submits to `admin-post.php` action `winserve_apply`; CV (PDF/DOC/DOCX, max 5 MB) is uploaded, emailed, then deleted.

### Customizer (`inc/customizer.php`)
Exposes theme_mod settings for: hero headline/subline/CTAs/bg-image, about section title/text/image, contact info (email, phone, address). Read these with `get_theme_mod( 'setting_key', 'default' )` in templates.

### Assets
- `assets/css/main.css` — global styles
- `assets/css/jobs.css` — job board only
- `assets/js/main.js` — global scripts; `MyBrand.ajaxUrl` and `MyBrand.nonce` localized here
- `assets/js/jobs.js` — job board only

CSS custom properties (brand tokens) are defined in `style.css` and mirror the tokens in `preview.html`.

## Brand Tokens
Primary palette: `--color-primary: #0E2A47` (navy), `--color-accent: #2B78BF` (blue), `--color-red: #C0392B`. Fonts: Inter (body), Poppins (headings).

## WP-CLI & Local Development

```bash
# Flush rewrite rules after changing CPT/taxonomy slugs
wp rewrite flush

# Create the Vacancies page and set front page
wp post create --post_type=page --post_title='Vacancies' --post_status=publish

# Export/import theme mods
wp theme mod get --all
```

WordPress is not in this repo — deploy `wp-content/` into an existing WordPress install (requires WP ≥ 6.0, PHP ≥ 8.0).

## Key Conventions
- All functions are prefixed `mybrand_` (theme utilities) or `winserve_` (business logic / CPT).
- Nonces: assessment form uses action `winserve_assessment_action`; job application uses `winserve_apply_action`; job meta save uses `winserve_job_save`.
- Text domain: `mybrand`.
- Image sizes registered: `portfolio-thumb` (600×450), `portfolio-large` (1200×900), `hero-banner` (1920×800).
