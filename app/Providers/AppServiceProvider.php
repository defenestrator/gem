<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Twitch\TwitchExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, TwitchExtendSocialite::class);

        // Allow overriding Pulse anonymous components (registered via anonymousComponentPath with prefix 'pulse')
        view()->prependNamespace(md5('pulse'), resource_path('views/vendor/pulse/components'));

        if (app()->isProduction()) {
            DB::listen(function (QueryExecuted $query) {
                if ($query->time > 1000) {
                    Log::channel('queries')->warning('Slow query', [
                        'sql'     => $query->sql,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }
    }
}
