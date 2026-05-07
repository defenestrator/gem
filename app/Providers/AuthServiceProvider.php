<?php

namespace App\Providers;

use App\Models\ContentSubmission;
use App\Models\User;
use App\Policies\ContentSubmissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ContentSubmission::class => ContentSubmissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user, string $ability) => $user->is_admin ? true : null);
    }
}
