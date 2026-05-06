# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Gem Reptiles —  a single-vendor Laravel 11 marketplace for buying/selling reptiles and invertebrates. Primary work is front-end views, data population, and feature enhancement on an existing schema/backend. Use standards-compliant modern PHP, AlpineJS, and Blade templates. We want to build the application with eventual multi-vendor features in mind, but we are not releasing that part of the application until further down the roadmap, 2028.

We are focused on synchronizing and managing our Store data on MorphMarket - "https://morphamrket/stores/gem" (first by consuming exported data from MorphMarket.com, eventually by exporting data to be uploaded to MorphMarket.com) while concurrently creating a viable e-commerce platform for vending live captive-bred reptiles.

## Local Reference Docs

Laravel 11 documentation is embedded at `storage/docs/laravel/` (Markdown, ~100 files). Use as primary reference before fetching from the web. Key files:

- `storage/docs/laravel/eloquent.md` — ORM, relationships, scopes
- `storage/docs/laravel/migrations.md` — schema builder
- `storage/docs/laravel/blade.md` — templating
- `storage/docs/laravel/requests.md` — HTTP requests, validation
- `storage/docs/laravel/scout.md` — full-text search (MeiliSearch)
- `storage/docs/laravel/queues.md` — jobs and queues
- `storage/docs/laravel/testing.md` — PHPUnit / Pest test helpers
- `storage/docs/laravel/authorization.md` — policies and gates

## Commands

```bash
php artisan migrate          # run migrations
php artisan test             # full test suite (Pest)
php artisan test --filter=FooTest  # single test
php artisan pint             # lint/format PHP
php artisan optimize         # cache config/routes/views after significant changes
npm run dev                  # Vite dev server
npm run build                # production assets
```

## Architecture

### Dual Data Source

Public-facing homepage and category pages (`/`, `/categories/*`) read from a flat JSON file at `storage/app/public/animals.json`. This file is uploaded by an admin via `AnimalImportController` (imported from MorphMarket). These routes use file-mtime-keyed cache (30 min TTL) and never hit the DB.

The `/animals` and `/classifieds` routes serve DB-backed models with full search/filter/pagination.

### Auth & Roles

`User.is_admin` gates all animal and classified CRUD. There is no seller-facing listing management — only admins can create/edit/delete `Animal` records. `Seller` is a profile linked to a `User` via `hasOne`, editable from the profile page.

### Key Models & Relationships

- `User` → `hasOne Seller`, `hasMany Animal`, `hasMany Classified`
- `Animal` → `belongsTo User`, `hasMany Classified`, `morphMany Media` (via `HasMedia` trait)
- `Classified` → `belongsTo User`, `morphMany Media`
- `Seller` → `belongsTo User`, `morphMany Media`

### Media

Custom `App\Models\Media` model with polymorphic `mediable` relation. **Not** spatie/laravel-medialibrary. The `HasMedia` trait (at `app/Models/Traits/HasMedia.php`) adds `morphMany(Media::class, 'mediable')` to any model. Images stored to `public` disk, URL saved in `media.url`.

### Slugs

`Sluggable` trait provides `createSlug()` / `getTitleFromSlug()` helpers. `Animal` slugs are set from the MorphMarket `Animal_Id*` field on import (not the pet name). `Classified` slugs are ULIDs. `Seller` slugs are `Str::slug(name) . '-' . random(6)`.

### Policies

Every model has a Policy registered in `AuthServiceProvider`. `AnimalPolicy` requires `is_admin` for all write operations. `ClassifiedPolicy` and others follow the same pattern — check before assuming any user can mutate data.

### Inquiry Flow

Public visitors submit inquiries via `/animals/{slug}/inquire` or `/classifieds/{slug}/inquire`. A queued `Mailable` is dispatched to the animal/classified owner's email. No auth required to inquire.

### Frontend Stack

Tailwind CSS 3 + Alpine.js for reactive UI and component state.

## Core Rules

- `Model::query()` not `DB::table()`
- No new composer packages without approval
- No new npm packages without approval
- Do not remove existing validation or business logic without approval
- Do not expose user PII without opt-in
- Write unit and integration tests for new features
- Always run `php artisan optimize` 
- Always run `php artisan test` after completing a changeset
- Always use the caveman skill caveman mode
- Always update `README.md` changelog after completing a feature changeset
- At the end of every functional changeset, after tests pass and README is updated, run: `git add . && git commit -m "{commit message}" && git push` — replace `{commit message}` with a concise, contextually correct message describing the changeset. - Do not credit Claude or Anthropic as co-authors. Remove the string "
- `AnimalAvailability` enum must be used for animal state — do not store raw strings
- For each changeset: Checkout git branch "main", git pull, then create a new branch with a name formatted like "feature/description-of-feature" and checkout the feature branch. Do not push to main.