[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Fd4a76f09-5d07-425e-a0f9-dda77044c23e&style=plastic)](https://forge.laravel.com/jeremy-anderson-okr/bright-viper/2088781)

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

#### 2026-05-04
- Added `species:import` Artisan command — imports Reptile Database CSV into `species` table, supports `--dry-run` and `--csv=` options, deduplicates on `species_number`
- Imported 11,440 species records from `reptile_checklist_2020_12.csv`
- Added `SpeciesType` enum (Syntype, Holotype, Lost, Paratype, Lectotype, Neotype)
- Added `SpeciesTypeCast` — parses space-delimited type tokens to `SpeciesType[]`; empty → `"null"`
- Updated `species` table: `type_species` bool → varchar(10), unique index on `species_number`
- Social auth buttons hidden on login and register views (pending re-enable)

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
