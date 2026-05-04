<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'facebook', 'twitch'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS), 404);

        try {
            $social = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Social login failed. Please try again.']);
        }

        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $social->getId())
            ->first();

        if ($account) {
            $account->update(SocialAccount::fromSocialite($provider, $social));
            Auth::login($account->user, remember: true);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // Link to existing user by email, or create new user
        $user = User::firstOrCreate(
            ['email' => $social->getEmail()],
            [
                'name'              => $social->getName() ?? $social->getNickname(),
                'password'          => Str::password(32),
                'email_verified_at' => now(),
            ]
        );

        $user->socialAccounts()->create(SocialAccount::fromSocialite($provider, $social));

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
