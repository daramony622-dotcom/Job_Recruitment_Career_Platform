<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Admin account ─────────────────────────────────────────────
            [
                'email'    => 'mengsiek8@gmail.com',
                'name'     => 'Super Admin',
                'password' => 'meng@123gris',
                'role'     => 'admin',
            ],
            [
                'email'    => 'mongsiek8@gmail.com',
                'name'     => 'Admin User',
                'password' => 'meng@123gris',
                'role'     => 'admin',
            ],
            [
                'email'    => 'daramony622@gmail.com',
                'name'     => 'Dara Mony',
                'password' => 'meng@123gris',
                'role'     => 'hr',
            ],
            [
                'email'    => 'monydara17@gmail.com',
                'name'     => 'Mony Dara',
                'password' => 'meng@123gris',
                'role'     => 'hr',
            ],
            [
                'email'    => 'satosiek697@gmail.com',
                'name'     => 'Sato Siek',
                'password' => 'meng@123gris',
                'role'     => 'user',
            ],
        ];

        foreach ($users as $account) {
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
            $this->command->info("✅ Seeded: {$account['email']}");
        }
    }
}