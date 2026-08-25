<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'mengsiek8@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('meng@123gris'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}