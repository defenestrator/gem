<?php

use Illuminate\Support\Facades\Route;

Route::permanentRedirect('/animals/gen-2025-corn-05-08', '/animals/gem-2025-corn-05-08');
Route::permanentRedirect('/animals/gen-2025-corn-05-11', '/animals/gem-2025-corn-05-11');

// Dashboard moderation routes consolidated into unified media queue
Route::name('dashboard.')->group(function () {
    Route::permanentRedirect('dashboard/species/media', '/dashboard/media')->name('species.media.index');
    Route::permanentRedirect('dashboard/subspecies/media', '/dashboard/media')->name('subspecies.media.index');
});
