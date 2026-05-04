<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

trait ValidatesTurnstile
{
    protected function verifyTurnstile(Request $request): void
    {
        $secret = config('services.turnstile.secret');

        if (! $secret) {
            return;
        }

        $token = $request->input('cf-turnstile-response');

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (! ($response->json('success') === true)) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Bot verification failed. Please try again.',
            ]);
        }
    }
}
