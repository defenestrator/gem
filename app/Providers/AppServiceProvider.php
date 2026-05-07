<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
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
    }
}
