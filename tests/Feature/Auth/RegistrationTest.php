<?php

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Http;

// Cloudflare always-pass test token (works with secret key 1x000...AA)
const TURNSTILE_VALID_TOKEN = 'XXXX.DUMMY.TOKEN.XXXX';

function fakesTurnstileSuccess(): void
{
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
    ]);
}

function fakesTurnstileFailure(): void
{
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']], 200),
    ]);
}

test('registration screen can be rendered', function () {
    $this->get('/register')->assertStatus(200);
});

test('new users can register with valid turnstile token', function () {
    fakesTurnstileSuccess();

    $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => TURNSTILE_VALID_TOKEN,
    ])->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticated();
});

test('registration fails without turnstile token', function () {
    $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('cf-turnstile-response');

    $this->assertGuest();
});

test('registration fails when turnstile verification is rejected', function () {
    fakesTurnstileFailure();

    $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'bad-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    $this->assertGuest();
});

test('honeypot filled redirects without creating user', function () {
    $this->post('/register', [
        'name'                  => 'Bot',
        'email'                 => 'bot@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'website'               => 'http://spam.example.com',
        'cf-turnstile-response' => TURNSTILE_VALID_TOKEN,
    ])->assertRedirect(RouteServiceProvider::HOME);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
});
