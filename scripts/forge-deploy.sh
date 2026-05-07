#!/bin/bash
set -e

cd /home/forge/gemreptiles.com

# Pull latest
git pull origin main

# PHP dependencies
$FORGE_PHP composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Cache config/routes/views
$FORGE_PHP artisan optimize

# Database
$FORGE_PHP artisan migrate --force

# Restart queue workers
$FORGE_PHP artisan queue:restart

# Warm app + CDN image thumbnail cache (hits app over localhost to avoid SSL)
$FORGE_PHP artisan app:warm --base-url=http://127.0.0.1 --images=thumbs --concurrency=25

echo "Deploy complete."
