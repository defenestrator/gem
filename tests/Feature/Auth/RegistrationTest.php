<?php

use App\Providers\RouteServiceProvider;

test('registration screen can be rendered', function () {
    $this->get('/register')->assertStatus(200);
});

test('new users can register', function () {
    $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(RouteServiceProvider::HOME);

    $this->assertAuthenticated();
});

test('honeypot filled redirects without creating user', function () {
    $this->post('/register', [
        'name'                  => 'Bot',
        'email'                 => 'bot@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
        'website'               => 'http://spam.example.com',
    ])->assertRedirect(RouteServiceProvider::HOME);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
});
