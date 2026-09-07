<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            [
                'name'     => 'Sokha Chea',
                'email'    => 'sokha.chea@gmail.com',
                'role'     => 'user',
                'city'     => 'Phnom Penh',
                'country'  => 'Cambodia',
                'headline' => 'Senior Frontend Developer',
            ],
            [
                'name'     => 'Dara Kim',
                'email'    => 'dara.kim@gmail.com',
                'role'     => 'user',
                'city'     => 'Siem Reap',
                'country'  => 'Cambodia',
                'headline' => 'Backend Engineer',
            ],
            [
                'name'     => 'Lina Sok',
                'email'    => 'lina.sok@gmail.com',
                'role'     => 'user',
                'city'     => 'Battambang',
                'country'  => 'Cambodia',
                'headline' => 'UX/UI Designer',
            ],
            [
                'name'     => 'Vichea Long',
                'email'    => 'vichea.long@gmail.com',
                'role'     => 'user',
                'city'     => 'Phnom Penh',
                'country'  => 'Cambodia',
                'headline' => 'Data Analyst',
            ],
            [
                'name'     => 'Rada Vong',
                'email'    => 'rada.vong@gmail.com',
                'role'     => 'user',
                'city'     => 'Kampong Cham',
                'country'  => 'Cambodia',
                'headline' => 'Mobile App Developer',
            ],
            [
                'name'     => 'Meas Pich',
                'email'    => 'meas.pich@gmail.com',
                'role'     => 'user',
                'city'     => 'Phnom Penh',
                'country'  => 'Cambodia',
                'headline' => 'Digital Marketing Specialist',
            ],
        ];

        foreach ($candidates as $candidate) {
            $user = User::updateOrCreate(
                ['email' => $candidate['email']],
                [
                    'name'              => $candidate['name'],
                    'password'          => Hash::make('Candidate@1234'),
                    'role'              => $candidate['role'],
                    'email_verified_at' => now(),
                ]
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'city'    => $candidate['city'],
                    'country' => $candidate['country'],
                    'headline' => $candidate['headline'],
                    'bio'     => "Experienced professional based in {$candidate['city']}, {$candidate['country']}.",
                ]
            );
        }
    }
}
