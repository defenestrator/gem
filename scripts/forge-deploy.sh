#!/bin/bash
set -e

cd /home/forge/gemreptiles.com

# Pull latest
git pull origin main

# PHP dependencies
$FORGE_PHP composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# JS assets
npm ci --prefer-offline
npm run build

# Database
$FORGE_PHP artisan migrate --force

# Restart queue workers
$FORGE_PHP artisan queue:restart

# Warm caches (hits app over localhost to avoid SSL issues)
$FORGE_PHP artisan app:warm --base-url=http://127.0.0.1

echo "Deploy complete."
