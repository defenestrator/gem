<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app()->isLocal()) {
            return;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v1/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $value,
        ]);

        if (! $response->successful() || ! $response->json('success')) {
            $fail('Bot verification failed. Please try again.');
        }
    }
}
