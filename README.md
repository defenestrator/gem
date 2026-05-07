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
