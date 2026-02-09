<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::updateOrCreate(['email' => 'admin@example.com'], ['name' => 'Admin User', 'password' => Hash::make('password'), 'role' => 'admin', 'wallet_balance' => 1000]);
User::updateOrCreate(['email' => 'teacher@example.com'], ['name' => 'Teacher User', 'password' => Hash::make('password'), 'role' => 'teacher']);
User::updateOrCreate(['email' => 'student@example.com'], ['name' => 'Student User', 'password' => Hash::make('password'), 'role' => 'student', 'wallet_balance' => 500]);

echo "Users Created Successfully\n";
