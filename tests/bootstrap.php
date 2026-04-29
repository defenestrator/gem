<?php

// Cached config bakes DB_DATABASE=gem into bootstrap/cache/config.php.
// PHPUnit env overrides (DB_DATABASE=gem_test) are silently ignored when
// config is cached, causing RefreshDatabase to wipe the live database.
// Delete the cache here, before Laravel boots, so env overrides always win.
$configCache = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($configCache)) {
    unlink($configCache);
}

require __DIR__ . '/../vendor/autoload.php';
