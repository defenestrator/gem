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
                'name'      => 'Jeremy',
                'password'  => config('app.admin_password'),
                'is_admin'  => true,
            ]
        );
    }
}
