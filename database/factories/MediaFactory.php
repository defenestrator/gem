<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url'               => 'https://gemx.sfo3.digitaloceanspaces.com/' . fake()->uuid() . '.jpg',
            'user_id'           => User::factory(),
            'mediable_type'     => null,
            'mediable_id'       => null,
            'moderation_status' => 'approved',
        ];
    }
}
