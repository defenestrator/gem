# CLAUDE.md: Reptile Marketplace Project

## Project Overview
A multi-vendor Laravel application for buying/selling exotic reptiles (snakes, lizards, chelonians) invertebrates (spiders, isopods, beetles), and related supplies. It has an existing data schema and some backend code, we are primarily implementing front-end views, populating data and enhnancing the existing code. Use standards-compliant modern PHP, vanilla javascript and blade templates whenever possible, do not take on new external code package dependencies without approval.

## Core Principles
1. **Reusability First:** Before creating new code, check for existing traits, services, policies, or helpers.
2. **Minimize Dependencies:** Do not add new `composer` packages unless absolutely required. Rely on Laravel core, php, tailwind and blade.
3. **Domain Focus:** Reptiles require specific data fields (scientific name, morph (genetic traits list), age, hatch date, and CITES status).
4. **Safety & Compliance:** Implement reliable validation for shipping reptile rates and legal regulations.

## Technical Guidelines

### 1. Database & Models
* Use Laravel Eloquent relationships for vendor products (`Seller` -> `Product`).
* Create a dedicated `Species` table/model to avoid repeated data entry.
* **Product Table Fields:** Use nullable fields for reptile-specific data (e.g., `morph`, `hatch_date`).
* Use `spatie/laravel-medialibrary` (existing) for image management (reptiles need multiple high-quality photos).

### 2. Code Organization
* **Actions:** Use Action classes (`App\Actions`) for complex business logic (e.g., `CreateReptileListing`, `ProcessOrder`) to keep controllers skinny.
* **Controllers:** Use common Laravel conventions for method names of CRUD functionality in Controllers, prefer creating a new Controller over adding more than five methods per controller. Acceptable method names include index(), create(), store(), show(), edit(), and destroy().
* **Traits:** Utilize Traits for shared logic across models (e.g., `HasSpecies` for the Taxonomic name of an animal, `HasMorph` for genetics and phenotype, `HasMedia` for images and other media files).
* **Services:** Create `App\Services` for interactions with shipping APIs or compliance checks.

### 3. Marketplace Functionality
* Use built-in Auth for Vendor/Buyer separation.
* Leverage Laravel's queue system for sending notifications to buyers regarding shipping updates.

### 4. Testing
* **Pest:** Use Pest for testing (`php artisan make:test --pest`).
* **Crucial Tests:** Focus on inventory management (prevent double selling) and vendor commission calculations.

## Reptile-Specific Constraints
* Listing validation must include **"captive-bred"** status.
* Use Enum for habitat types (terrestrial, arboreal, aquatic).

## Commands
* `php artisan migrate` - Run migrations.
* `php artisan test` - Run test suite.
* `npm run dev` - Start Vite.

## Rules
* ALWAYS use `Model::query()` instead of `DB::table()`.
* DO NOT remove existing validation or business logic without approval.
* DO NOT expose user PII without opting in. 
* DO NOT create code with security vulnerabilities
* ALWAYS follow safe coding best-practices for PHP and Laravel
* ALWAYS follow secure coding best-practices for JavaScript
