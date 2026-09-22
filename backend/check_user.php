<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Count all users
$total = User::count();
echo "Total users in DB: " . $total . PHP_EOL;

// List first 10
$users = User::limit(10)->get(['id', 'name', 'email', 'role', 'email_verified_at', 'password']);
foreach ($users as $u) {
    echo "  [{$u->id}] {$u->email} | role={$u->role} | verified=" . ($u->email_verified_at ? 'YES' : 'NO') . " | has_pw=" . ($u->password ? 'YES' : 'NO') . PHP_EOL;
}

// Try to create a test admin user if none exist
if ($total === 0) {
    echo PHP_EOL . ">>> Creating test admin user..." . PHP_EOL;
    $admin = User::create([
        'name'              => 'Admin',
        'email'             => 'admin@gmail.com',
        'password'          => Hash::make('admin123'),
        'role'              => 'admin',
        'is_active'         => true,
        'email_verified_at' => now(),
    ]);
    echo "Created admin: {$admin->email} / password: admin123" . PHP_EOL;

    // Also create the user from the screenshot
    $regular = User::create([
        'name'              => 'Mongsiek',
        'email'             => 'mongsiek8@gmail.com',
        'password'          => Hash::make('meng@123gris'),
        'role'              => 'job_seeker',
        'is_active'         => true,
        'email_verified_at' => now(),
    ]);
    echo "Created user: {$regular->email} / password: meng@123gris" . PHP_EOL;
}