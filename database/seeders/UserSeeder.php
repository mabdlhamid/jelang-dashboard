<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        User::create([
            'name' => 'Admin Kasir',
            'email' => 'admin@cafe.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Owner user
        User::create([
            'name' => 'Cafe Owner',
            'email' => 'owner@cafe.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        echo "✅ Users created successfully!\n";
        echo "   Admin: admin@cafe.com / password\n";
        echo "   Owner: owner@cafe.com / password\n";
    }
}