<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'provider'         => fake()->randomElement(['google', 'facebook', 'twitch']),
            'provider_id'      => fake()->numerify('##########'),
            'name'             => fake()->name(),
            'email'            => fake()->safeEmail(),
            'nickname'         => fake()->userName(),
            'avatar'           => fake()->imageUrl(),
            'token'            => fake()->sha256(),
            'refresh_token'    => fake()->sha256(),
            'token_expires_at' => now()->addHour(),
            'provider_data'    => [],
        ];
    }
}
