<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'jeremyblc@gmail.com'],
            [
                'name'               => 'Jeremy',
                'email'              => 'jeremyblc@gmail.com',   
                'password'           => env('app.admin_password'),
                'is_admin'           => true,
                'email_verified_at'  => now(),
            ]
        );
    }
}
