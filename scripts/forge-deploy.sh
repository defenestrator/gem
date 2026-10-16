#!/bin/bash
set -euo pipefail

cd "${FORGE_SITE_PATH:-/home/gemreptiles/gemreptiles.com}"

# Pull latest
git pull --ff-only origin main

# PHP dependencies
# Forge may supply "php8.3 /usr/local/bin/composer" rather than one executable.
# Split words into an array; never evaluate shell syntax from environment values.
IFS=' ' read -r -a composer_command <<< "${FORGE_COMPOSER:-composer}"
"${composer_command[@]}" install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Fetch Vite manifest from CDN (built and uploaded by CI before this hook fires)
mkdir -p public/build
curl -sf "https://gemx.sfo3.digitaloceanspaces.com/build/manifest.json" -o public/build/manifest.json

# Clear stale config, migrate, then rebuild caches for the new schema.
$FORGE_PHP artisan optimize:clear
$FORGE_PHP artisan migrate --force
export GEM_DEPLOY_REVISION="$(git rev-parse HEAD)"
$FORGE_PHP artisan optimize

# Restart queue workers
$FORGE_PHP artisan queue:verify-horizon --restart --wait=90

# Warm app + CDN image thumbnail cache (hits app over localhost to avoid SSL)
$FORGE_PHP artisan app:warm --base-url=http://127.0.0.1 --images=thumbs --concurrency=25

# Flush DO Spaces CDN cache so users get fresh assets immediately
# $FORGE_PHP artisan cdn:flush

echo "Deploy complete."
