[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Fd4a76f09-5d07-425e-a0f9-dda77044c23e&style=plastic)](https://forge.laravel.com/jeremy-anderson-okr/bright-viper/2088781)

[![CI](https://github.com/defenestrator/gem/actions/workflows/ci.yml/badge.svg)](https://github.com/defenestrator/gem/actions/workflows/ci.yml)

## About gemreptiles.com v3

We are a boutique hobbyist reptile breeding operation owned and operated by the love of my life, Becky and myself. I do all the web/business development, some of the husbandry and all of the marketing. Becky does most of the husbandry, even though I have the most experience with reptiles, we have more than 60 years of combined experience keeping and breeding exotics.

The primary marketplace for selling reptiles online is morphmarket.com. This site imports our inventory from MorphMarket and presents it on gemreptiles.com with our own branding and storefront experience.

### Tech Stack

- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Tailwind CSS 3, Alpine.js
- **Database:** PostgreSQL
- **Cache:** Redis (predis)
- **Mail:** SendGrid
- **Assets:** Vite

### Application Architecture

The app has two data paths for animal listings:

1. **JSON import (public storefront):** An admin uploads a JSON export from MorphMarket via the dashboard. This file is saved to `storage/app/public/animals.json` and drives the homepage, category pages, and category filtering. These routes are cached by file mtime (30 min TTL) and never hit the database.

2. **Database-backed listings:** The `/animals` and `/classifieds` routes serve Eloquent-backed records with full search, filtering, and pagination. Animals are synced from the JSON import into the `animals` table via `AnimalImportController`.

Auth uses a simple `is_admin` flag on `User`. Only admins can create, edit, or delete animal records. The `Seller` model is a profile linked 1:1 to a `User` and is editable from the profile page.

### MorphMarket JSON Import Format

The dashboard import accepts the JSON export from MorphMarket. Key fields used by the importer:

| Field | Description |
|---|---|
| `Animal_Id*` | Used as the slug |
| `Title*` | Pet name |
| `Category*` | Species category (e.g. Ball Pythons, Corn Snakes) |
| `State` | `For Sale`, `Breeder`, `Sold`, `Not For Sale` |
| `Enabled` | `Active` or inactive |
| `Visibility` | `Public` or private |
| `Price` | Listing price |
| `Dob` | Date of birth (supports `n/j/Y`, `n/Y`, `Y` formats) |
| `Sex` | `male` / `female` |
| `Photo_Urls` | Space-separated image URLs |
| `Mm_Url**` | Link back to the MorphMarket listing |
| `Desc` | Description |

### Feature Flags

| Flag | Default (prod) | Default (non-prod) | Description |
|---|---|---|---|
| `FEATURE_CLASSIFIEDS` | `false` | `true` | Enables the classifieds marketplace feature |

### Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
```

For local development:

```bash
npm run dev        # Vite dev server with HMR
php artisan serve  # or use Laravel Herd / Valet
```

### Notes

The source is open. Our content, logos, and UI designs are &copy; All Rights Reserved 2024–2026.

Everything except secrets belongs in git. Blobs go in S3 or similar — not in the database, not in git.

### Changelog

#### 2026-05-14 (performance: indexes, inventory service, slow query logging)
- Migration `add_performance_indexes`: composite index on `animals(status, availability, created_at)`; composite on `classifieds(status, price, created_at)`; partial index on `media(mediable_type, mediable_id) WHERE moderation_status = 'approved'`; partial index on `animals(species_id) WHERE status = 'published'`; index on `species(higher_taxa)`; GIN trigram indexes on `animals.pet_name` and `animals.description` for fast fallback LIKE search (enables `pg_trgm` extension)
- `AnimalInventoryService`: extracts JSON load/sort/filter logic from `routes/web.php`; per-sort-variant mtime-keyed Redis cache; active-only filtering moved to PHP before view render
- `routes/web.php`: welcome, categories, and all 5 category routes now use `AnimalInventoryService`; cache keys normalized to `welcome:{sort}:{mtime}`, `categories:{mtime}`, `categories:{view}:{mtime}`; species eager-load selects only needed columns
- `Media::scopeApprovedThumbnail()`: approved + featured-first + limit 1 for efficient single-thumbnail eager loads
- `AppServiceProvider`: slow query listener logs queries >1s to `queries` channel in production only
- `config/logging.php`: `queries` channel added (`storage/logs/queries.log`, warning level)

#### 2026-05-14 (Turnstile hardening — botnet response)
- Fixed Alpine/Turnstile race condition: replaced `@submit.prevent="submitWithTurnstile($el)"` with `onsubmit="return submitWithTurnstile(this)"` on all forms — no Alpine dependency, no event ordering issues
- `submitWithTurnstile` now targets the specific `.cf-turnstile` DOM element (not `.cf-turnstile` class selector) for `turnstile.reset()` and `turnstile.execute()` — fixes multi-form pages
- `onTurnstileVerified` now locates hidden input via `form.querySelector('[name="cf-turnstile-response"]')` rather than a global `getElementById` — correct in all multi-form scenarios
- Added `<x-turnstile />` + `onsubmit` handler to: `auth/login`, `auth/forgot-password`, `support/create` (were unprotected)
- Added server-side `verifyTurnstile()` via `ValidatesTurnstile` trait to 6 controllers: `RegisteredUserController`, `AuthenticatedSessionController`, `PasswordResetLinkController`, `SupportTicketController`, `InquiryController`, `ClassifiedInquiryController` (none had server-side verification before)
- `phpunit.xml`: override `TURNSTILE_SECRET_KEY` and `TURNSTILE_SITE_KEY` to empty so tests bypass Turnstile verification (auth tests don't test bot protection)

#### 2026-05-08 (featured image selection for Animals and Species)
- Added `is_featured` boolean to `media` table; one featured media per mediable entity
- Admin star-picker UI on Animal and Species show pages via Livewire v3 `FeaturedMediaPicker` component + Alpine.js
- Star is mutually exclusive per entity (radio-style): clicking sets featured, clears others for same mediable
- `PATCH /dashboard/media/{media}/feature` route via `DashboardMediaModerationController::setFeatured`
- Animal and Species index thumbnails now prefer featured media over first media
- `featuredMedia()` + `featuredApprovedMedia()` relationships added to `HasMedia` trait and `Species` model

#### 2026-05-08 (automated backups: spatie/laravel-backup)
- Installed `spatie/laravel-backup` v9 with `spatie/db-dumper` (PostgreSQL support)
- DB-only backup every 6 hours; full backup daily at 02:00; cleanup daily at 01:00
- All backups push to `private_s3` disk → DO Spaces "privates" bucket under `backups/` prefix
- Retention: all backups kept for 12 days (~48 DB backups), then 1/day until 30 days, then pruned
- Backups only run in the `production` environment; gzip compression on DB dumps
- Added `BACKUP_ARCHIVE_PASSWORD` and `BACKUP_NOTIFICATION_EMAIL` to `.env.example`
- Note: manually create `backups/db` and `backups/laravel` subdirectories in DO Spaces if desired

#### 2026-05-08 (IncomingEmailController + Email CRM)
- `POST /email/inbound` (`email.inbound`) handles SendGrid Inbound Parse webhooks
- Parses `from`/`to`/`subject`/`text`/`html` + SendGrid `envelope` JSON; deduplicates by `Message-ID` header
- Threads messages by contact email + base subject (Re:/Fwd: stripped); reopens closed conversations on new mail
- Forwards to all `is_admin` users via queued `ForwardedInboundEmailMail` (was hardcoded; now dynamic)
- Admin CRM at `/dashboard/conversations`: list with status tabs (open/closed/spam/all), thread view, reply, status update, delete
- `ConversationReplyMail` queues reply to contact with `Re: {subject}` threading
- Fixed broken `VerifyCsrfToken::$except` (was array-of-array with route name; changed to URI string `email/inbound`)
- `email_conversations` + `email_messages` tables; `EmailConversation` / `EmailMessage` models

#### 2026-05-08 (support ticket: fix email verification flow)
- New support ticket users now also receive a password reset link so they can log in before clicking the verification link
- Without a known password, users had no path to authenticate and complete email verification

#### 2026-05-08 (rebrand Seller/Vendor → Social Media / Profiles)
- Routes `/sellers` → `/profiles`, names `sellers.index/show` → `profiles.index/show`
- Nav label "Sellers" → "Profiles"
- Profile section heading "Vendor Profile" → "Social Media & Profile"; description and save button updated
- Onboarding checklist item "Set up your vendor profile…" → "Add your social media links to your profile"
- Sellers index/show: visible text and empty-state copy updated to "Profiles" / "breeder"

#### 2026-05-07 (Open Graph / link previews)
- Both layouts: default `og:site_name`, `og:type`, `og:url`, `og:title`, `og:description`, `og:image` + Twitter card tags using `og-default.jpg` from CDN
- `animals/show`: overrides `og:type=product`, `og:title`, `og:description`, `og:image` with animal's first media photo
- `species/show`: overrides `og:title`, `og:description`, `og:image` with first approved species media photo
- Any page can add `@section('og_image', ...)` / `@section('og_title', ...)` / `@section('og_description', ...)` to customize

#### 2026-05-07 (support ticket form)
- New `support_tickets` table (name, email, type, message, user_id FK nullable)
- `SupportTicketController`: validates input, creates a new `User` with a secure random password when the email is unknown, fires `Registered` event to send verification email, links existing users without re-registering, creates ticket, queues `SupportTicketAdminMail` to all admin users
- `SupportTicketAdminMail` + `emails/support-ticket-admin.blade.php` admin notification email
- `GET /support` / `POST /support` (support.create / support.store)
- Support link added to site footer under Legal column

#### 2026-05-07 (performance: SSR species init + CDN preconnect)
- Species search index: `SpeciesController::index()` pre-fetches page-1 results using same Redis cache key as `search()`; results injected as `window.__speciesInitial__`; Alpine `init()` consumes SSR data directly when in default state (no query/taxon/hasMedia/page=1), skipping the initial XHR entirely
- Both layouts: `<link rel="preconnect" href="https://gemx.sfo3.digitaloceanspaces.com" crossorigin>` — browser opens TLS connection before first image request
- welcome.blade.php: `decoding="async"` on all non-LCP lazy-loaded animal card images
- Species thumbnails sized to 100×100px (up from 40×40px); `fetchpriority="high"` on first result row image; `loading="lazy"` bound per-row via Alpine `$index`
- Axios removed from JS bundle (`bootstrap.js`); all XHR uses native `fetch()` — bundle 84KB → 45KB
- CSS bundle: removed invalid `@tailwind forms;` directive and redundant compiled-views glob from tailwind.config.js — bundle 81KB → 71KB
- Async CSS loading in production via `rel="preload" as="style" onload` swap with `<noscript>` fallback; inline critical `background-color` prevents FOUC in guest layout
- welcome.blade.php: LCP image computed server-side, `<link rel="preload" as="image">` injected in `<head>`, first card image `fetchpriority="high"` (no `loading="lazy"`), rest lazy+decoding=async; `width="800" height="800"` on all card images (CLS)
- Removed `x-transition` from Alpine spinner and clear button (prevented forced reflow on layout-property reads)
- 301 redirects for all favicon/manifest paths via `routes/web.php` loop; `scripts/nginx-favicons.conf` added for Forge nginx config to pass favicon paths through to PHP

#### 2026-05-07 (animal media pipeline)
- `media:process-animals` command: syncs `animals/` prefix from DO Spaces, generates 400×400 square JPEG thumbnails via Intervention/Image, recompresses originals at Q85, syncs optimized originals and `thumbs/animals/` back to DO Spaces, updates `media.thumbnail_url` per record
- `animals:sync` now calls `media:process-animals` after `animals:mirror-media`; JSON rewrite adds `Thumbnail_Url` field (first media record's thumbnail) alongside existing `Photo_Urls`
- `welcome.blade.php`: non-LCP animal cards use `Thumbnail_Url ?? Photo_Urls[0]`; LCP card always uses full-size original
- `animals/index.blade.php`: card `img src` uses `thumbnail_url ?? url`; LCP preload hint uses thumbnail

#### 2026-05-07 (species media pipeline)
- `media:process-species` command: syncs `species/` prefix from DO Spaces to `storage/app/spaces/`, generates 100×100 square JPEG thumbnails via Intervention/Image (Imagick driver), recompresses JPEG originals at Q85, syncs optimized originals and new `thumbs/species/` prefix back to DO Spaces, and updates `media.thumbnail_url` idempotently
- `media.thumbnail_url` column added; `SpeciesController::format()` serves `thumbnail_url ?? url` so the species search index immediately uses 100px thumbnails once generated
- `config/image.php` created; Intervention/Image configured to use Imagick driver
- Command options: `--dry-run`, `--force`, `--no-sync`, `--skip-optimize`, `--batch=N`

#### 2026-05-07 (welcome page + favicon)
- `welcome.blade.php`: `title` attributes on all sort/action/external links; richer `alt` text on animal images (name + category + sex + traits); `loading="lazy"` on animal images; `rel="noopener noreferrer"` on MorphMarket external links; invalid `<h2 href>` element corrected
- Favicon: both layouts now identical — removed redundant non-standard 100x100/192x192/256x256 `rel="icon"` links from guest layout (webmanifest covers Android/PWA sizes); added `msapplication-TileColor` to both layouts

#### 2026-05-07 (SEO + performance)
- Meta descriptions added to all public-facing routes: homepage, category pages, animals index/show, species index/show, subspecies show, sellers index/show, classifieds index/show
- `@stack('meta')` added to both layouts (`app.blade.php`, `guest.blade.php`) for per-page meta injection
- Page titles updated to use `Gem Reptiles` consistently in both layouts; typo in guest layout title fixed
- Bunny.net font loading changed from render-blocking `<link rel="stylesheet">` to non-blocking `rel="preload"` + `onload` swap with `<noscript>` fallback; eliminates font-induced paint delay
- Species search thumbnails: added `width="40" height="40"` (prevents CLS) and `loading="lazy"` (defers off-screen image fetches)

#### 2026-05-07
- Cloudflare Turnstile rewritten: widget now uses `data-execution="execute"` + `data-appearance="interaction-only"` — challenge runs on form submit (via `submitWithTurnstile()`), not on page load; eliminates Safari password-save dialog race condition that was invalidating tokens
- Turnstile component owns the `cf-turnstile-response` hidden input (`data-response-field="false"`); widget resets can no longer clear the token
- `UserSeeder`: 32 verified + 1 unverified non-admin users via Faker `safeEmail`
- CI `assets` job: declare `environment: Production` to unlock environment secrets/vars; use `vars.ASSET_URL` (not `secrets`)
- Forge deploy: fetch `manifest.json` from DO Spaces CDN via `curl` (public-read, no-cache headers); `optimize:clear` before `optimize` to nuke stale view cache
- Frontend assets build and CDN sync moved to CI `assets` job (runs after tests pass on main); Forge no longer builds JS
- `manifest.json` synced with `no-cache` headers separately from hashed assets (`immutable`); deploy hook fires only after sync completes
- Vite `base` only applied in `production` mode; stale `public/hot` file deleted

#### 2026-05-06
- "Back to Dashboard" link moved into Pulse header via anonymous component override (`resources/views/vendor/pulse/components/pulse.blade.php`); registered via `prependNamespace` in `AppServiceProvider` to override Pulse's `anonymousComponentPath`
- Vite config fixed: `base` (CDN asset URL) now only applied in `production` mode; dev mode no longer pollutes `public/hot` with CDN path, preventing stale hot file asset routing errors
- Nav renamed "Photos" → "Media" (admin-only nav link)
- Animal slug added to all inquiry email subjects (`AnimalInquiryMail`, `InquiryConfirmationMail`, `InquiryAdminNotificationMail`)

#### 2026-05-06 (species-admin)
- Admin can edit all fields on species (`species`, `common_name`, `author`, `higher_taxa`, `species_number`, `changes`, `description`) via `dashboard/species/{id}/edit`; same for subspecies (`genus`, `species`, `subspecies`, `author`, `description`) via `dashboard/subspecies/{id}/edit`
- Admin can detach photos from species/subspecies (hard-deletes DB row, file stays on S3) via hover-reveal Detach button on show pages and edit pages
- Authenticated (non-admin) users can submit description proposals on species and subspecies detail pages; submissions go to `species_content_submissions` table with `pending` status
- Admin moderation queue at `dashboard/submissions`: approve (writes `proposed_value` to `description`) or reject; reviewer and timestamp recorded
- `media.deleted_at` column added (scaffolding for future soft-delete media library feature)

#### 2026-05-06
- `species:import-checklist` command imports data from the Reptile Database XLSX checklist: 1,288 new species, 219 new subspecies, 9 taxonomy change notes, 53 `type_species` flag corrections; joins on `sp_id`/`species_number`; `--dry-run` and `--task` options (species|changes|type_species|subspecies|all)
- Species index back button now restores previous page number via `species_page` sessionStorage key
- Deploy hook: CI triggers POST to `DEPLOY_HOOK` GitHub Secret after tests pass on main

#### 2026-05-06 (feature/content-updates)
- Species biography generation: `species:generate-bios` queues `GenerateSpeciesBiographyJob` per record; sources Wikipedia, iNaturalist, GBIF; outputs structured Markdown; skips existing bios unless `--force`; `--limit`, `--model`, `--id`, `--dry-run` options
- `species:work-bios` queue worker command for the `species-bios` queue
- `species:normalize-bios` command converts Wikipedia `== heading ==` markup to Markdown headings in stored descriptions; `--dry-run` and `--model` options
- Wikipedia markup normalization applied directly to 7,084 existing species/subspecies rows via SQL `regexp_replace`
- Biography job now normalizes Wikipedia markup at generation time (headings, bold/italic, links, templates)
- Bios rendered as Markdown HTML (`Str::markdown`) in species and subspecies detail views, positioned below the photo gallery
- `species:sync-taxonomy` cross-checks all species against GBIF backbone; flags synonyms in the `changes` field (`GBIF-SYNONYM:date:accepted_name`); fills empty `common_name` from GBIF vernacular names; subspecies-safe (skips missing columns); options: `--dry-run`, `--model`, `--limit`, `--min-confidence`, `--force`, `--genus`, `--family`
- Taxonomy sync run: 68 species flagged as synonyms (notable: Trimeresurus→Craspedocephalus, Lygosoma→Riopa/Subdoluseps, Lobulia→Alpinoscincus/Nubeoscincus); 480 common names filled
- Species index pagination: links now appear both above and below the results table; results per page reduced from 100 to 80
- `species:export-bios` fixed: `chunkById` now includes `id` in select; writes to local disk via `file_put_contents` (default disk is S3)
- `database/sql/` added to `.gitignore` (scratch SQL scripts)

#### 2026-05-06
- Installed `keepsuit/laravel-opentelemetry` package for OpenTelemetry integration, enabling tracing and metrics collection
- Installed `laravel/pulse` package for real-time application performance monitoring and analytics dashboard, compatible with Alpine.js and Blade templates
- Configured Pulse with database migrations and published configuration/assets
- Published OpenTelemetry configuration files for further customization
- Added admin-only "Monitoring" navigation link in dashboard Quick Actions to access Laravel Pulse dashboard
- Added "Back to Dashboard" link in Pulse monitoring page header for easy navigation back to admin dashboard

#### 2026-05-04 (continued)
- `species:fetch-images` source chain expanded to 7 sources: added Reptile Database (HTML scrape, genus/species/subspecies URL params), ARMI USGS gallery (public domain government images), BioLib.cz (3-step HTML scrape, 2 s rate-limit between requests)
- `logs:upload` now truncates each log file after successful S3 upload so subsequent runs start clean
- Species search: default alphabetical browse (100/page, paginated); text search returns flat results (no limit, cached 5 min); browse/taxa results cached 1 hour; Redis cache key prefix `species_search:`; sessionStorage LRU result cache (`species_rc_` prefix, 20-entry max)
- Species search filter: "Has photos" checkbox + 6 mutually exclusive taxon pill buttons (Lizards, Snakes, Geckos, Turtles & Tortoises, Amphisbaenia, Crocodilians); taxon state persisted in `sessionStorage`
- Species detail view: reactive attribution bar below gallery updates on thumbnail click (title, author, license, source link, "Full attribution" link)
- Added media attribution page at `/media/{id}/attribution`; linked from species/subspecies detail views
- Clear search button always visible; resets query, taxon filter, and sessionStorage state

#### 2026-05-04 (continued)
- Added `logs:upload` Artisan command — streams `storage/logs/*.log` to `private_s3` disk; scheduled monthly on the 15th at midnight
- Added `private_s3` filesystem disk (`PRIVATE_S3_KEY`, `PRIVATE_S3_SECRET`, `PRIVATE_S3_BUCKET`, `PRIVATE_S3_REGION`, `PRIVATE_S3_ENDPOINT`) — separate credentials from public media S3; visibility private
- Added `FetchTaxonImageJob` queued job — dispatches one job per species/subspecies record on the `species-images` queue; `species:fetch-images --model=all --queue` enqueues all unprocessed taxa
- Scheduled `species:fetch-images --model=all --queue` weekly, Sundays at 03:00 `America/Boise`
- `species:fetch-images` now accepts `--queue` flag to dispatch `FetchTaxonImageJob` per record instead of processing inline; `--max` option controls images per taxon (default 1); `buildQuery` orders 0-image records first so re-runs always make forward progress; default `--limit` bumped to 1,000
- Image license filter relaxed: null/unspecified licenses accepted; only "all rights reserved" explicitly rejected
- Photo column moved to leftmost position in species search results table

#### 2026-05-04 (continued)
- Production database migrated from MySQL 8 to PostgreSQL
- Added `species:fetch-images` Artisan command — fetches free CC-licensed images for species and subspecies from a four-source chain: Wikipedia REST API → Wikimedia Commons direct file search → iNaturalist taxa API → GBIF species media API; resumable batched runs (`--limit`, `--model`, `--id`), `--dry-run` and `--force` options, 500ms rate-limiting delay between requests
- Added `media:export-species` — exports approved species/subspecies media (with attribution) to a portable JSON file for production import
- Added `media:import-species` — idempotent JSON import for production; matches records by scientific name (not ID) for cross-environment safety
- Added `source_url` and `license_url` columns to `media` table; existing `license`, `author`, `copyright` columns reused for full attribution storage
- Scientific names now displayed (italic, linked to species record) on animal index cards, animal detail pages, and the homepage welcome cards
- Species search results now show a thumbnail of the most recent approved photo instead of the type-specimen badge column
- Species search query now persists in `sessionStorage`; random seed only fires on first visit or after the field is cleared
- Added `latestApprovedMedia()` morphOne relationship to `Species` model; used by the search API to return thumbnails in a single eager-loaded query

#### 2026-05-04 (continued)
- Applied patches to production server for kmod vulnerability
- Applied patches to production server for kmod vulnerability

#### 2026-05-04 (continued)
- Added `species:import` Artisan command — imports Reptile Database CSV into `species` table, supports `--dry-run` and `--csv=` options, deduplicates on `species_number`
- Imported 11,440 species records from `reptile_checklist_2020_12.csv`
- Added `SpeciesType` enum (Syntype, Holotype, Lost, Paratype, Lectotype, Neotype)
- Added `SpeciesTypeCast` — parses space-delimited type tokens to `SpeciesType[]`; empty → `"null"`
- Updated `species` table: `type_species` bool → varchar(10), unique index on `species_number`
- Added MeiliSearch full-text species search (`laravel/scout`, `meilisearch/meilisearch-php`)
- Species search view with real-time Alpine.js UI, debounced input, dual-layer cache (client session + server 5-min TTL)
- Species detail view (`/species/{id}`) — taxonomy info card, approved photo gallery, user photo upload form
- Photo moderation pipeline: user uploads set `moderation_status = pending`; admin dashboard reviews/approves/rejects
- Added `moderation_status` column to `media` table (default `approved` — existing media unaffected)
- Admin nav "Photos" badge shows count of pending species photos
- Social auth buttons hidden on login and register views (pending re-enable)
- Added `animals:backfill-species` Artisan command — matches `Animal.category` to species via `common_name` LIKE, supports `--dry-run` and `--force-first`; hardcoded overrides for Western Hognose (Heterodon nasicus) and Coastal Carpet Pythons (Morelia spilota)
- Added `species_id` FK column to `animals` table (nullable, `nullOnDelete`)
- Added `Animal→Species` `belongsTo` relationship and `Species→Animal` `hasMany`
- Embedded Laravel 11 docs in `storage/docs/laravel/` for local Claude inference reference
- Added Cloudflare Turnstile bot protection to animal and classified inquiry forms; server-side verification via `ValidatesTurnstile` trait; disabled automatically when `TURNSTILE_SITE_KEY` not set
- Species photo uploads use Digital Ocean Spaces (S3-compatible, `sfo3`); admin uploads bypass moderation and publish immediately
- Configured `league/flysystem-aws-s3-v3` S3 adapter; `visibility: public` default on `s3` disk

#### 2026-04-30
- Added social auth (Google, Facebook, Twitch) via Laravel Socialite
- Added `SocialAccount` model and `social_accounts` migration

#### 2026-04-27
- Added `FEATURE_CLASSIFIEDS` feature flag (disabled on production by default)
- Removed HTMX and Hyperscript; Alpine.js only

#### 2024-04-24 Major overhaul and launch preparation

- Updated to Laravel 11
- Removed UUIDs from models
- Removed Dyrynda's deprecated UUID packages
- Updated package.json and composer.json dependencies
- Removed Daisy UI
- Added @tailwindcss/typography
- Updated root .gitignore
