<?php

use App\Models\SocialAccount;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function makeSocialiteUser(string $id, string $name, string $email, string $nickname = ''): SocialiteUser
{
    $user = Mockery::mock(SocialiteUser::class);
    $user->token = 'test-token';
    $user->refreshToken = 'test-refresh';
    $user->expiresIn = 3600;
    $user->shouldReceive('getId')->andReturn($id);
    $user->shouldReceive('getName')->andReturn($name);
    $user->shouldReceive('getEmail')->andReturn($email);
    $user->shouldReceive('getNickname')->andReturn($nickname);
    $user->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $user->shouldReceive('getRaw')->andReturn(['id' => $id, 'name' => $name, 'email' => $email]);

    return $user;
}

function mockDriver(string $provider, SocialiteUser $socialUser): void
{
    $driver = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
    $driver->shouldReceive('redirect')->andReturn(redirect('/'));
    $driver->shouldReceive('user')->andReturn($socialUser);

    Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
}

// ── Redirect ─────────────────────────────────────────────────────────────────

test('social redirect returns redirect for valid providers', function (string $provider) {
    $driver = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
    $driver->shouldReceive('redirect')->andReturn(redirect('/'));
    Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);

    $this->get("/auth/{$provider}")->assertRedirect();
})->with(['google', 'facebook', 'twitch']);

test('social redirect returns 404 for invalid provider', function () {
    $this->get('/auth/twitter')->assertNotFound();
});

// ── Callback: new user ────────────────────────────────────────────────────────

test('new user created and logged in via social callback', function (string $provider) {
    $social = makeSocialiteUser('social-123', 'Jane Doe', 'jane@example.com', 'janedoe');
    mockDriver($provider, $social);

    $this->get("/auth/{$provider}/callback")
        ->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $this->assertDatabaseHas('social_accounts', [
        'provider'    => $provider,
        'provider_id' => 'social-123',
    ]);
})->with(['google', 'facebook', 'twitch']);

test('new social user has email verified immediately', function () {
    $social = makeSocialiteUser('g-999', 'New User', 'new@example.com');
    mockDriver('google', $social);

    $this->get('/auth/google/callback');

    expect(User::where('email', 'new@example.com')->first()->email_verified_at)->not->toBeNull();
});

// ── Callback: existing social account ─────────────────────────────────────────

test('existing social account logs user in and refreshes tokens', function () {
    $user = User::factory()->create();
    SocialAccount::factory()->create([
        'user_id'     => $user->id,
        'provider'    => 'google',
        'provider_id' => 'g-existing-456',
    ]);

    $social = makeSocialiteUser('g-existing-456', $user->name, $user->email);
    mockDriver('google', $social);

    $this->get('/auth/google/callback')
        ->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticatedAs($user);
});

// ── Callback: email link ───────────────────────────────────────────────────────

test('social login links to existing user with matching email', function () {
    $existing = User::factory()->create(['email' => 'existing@example.com']);

    $social = makeSocialiteUser('fb-789', 'Existing User', 'existing@example.com');
    mockDriver('facebook', $social);

    $this->get('/auth/facebook/callback')
        ->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticatedAs($existing);
    expect(SocialAccount::where('user_id', $existing->id)->count())->toBe(1);
});

// ── Callback: provider data stored for BI ─────────────────────────────────────

test('raw provider data stored in social account for BI', function () {
    $social = makeSocialiteUser('tw-101', 'StreamerGuy', 'streamer@example.com', 'streamerguy');
    mockDriver('twitch', $social);

    $this->get('/auth/twitch/callback');

    $account = SocialAccount::where('provider', 'twitch')->first();
    expect($account->provider_data)->toBeArray()->not->toBeEmpty();
    expect($account->nickname)->toBe('streamerguy');
});

// ── Callback: error handling ───────────────────────────────────────────────────

test('socialite exception redirects to login with error', function () {
    $driver = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
    $driver->shouldReceive('user')->andThrow(new \Exception('OAuth error'));
    Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

    $this->get('/auth/google/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
});

test('invalid provider callback returns 404', function () {
    $this->get('/auth/snapchat/callback')->assertNotFound();
});
