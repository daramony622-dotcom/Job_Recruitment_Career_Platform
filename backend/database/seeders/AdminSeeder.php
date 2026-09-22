<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'email' => 'mengsiek8@gmail.com',
                'name'  => 'Super Admin',
                'password' => 'meng@123gris',
                'role'  => 'admin',
            ]
        ];

        foreach ($admins as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name'              => $account['name'],
                    'password'          => Hash::make($account['password']),
                    'role'              => $account['role'],
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}