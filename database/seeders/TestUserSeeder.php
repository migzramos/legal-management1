<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin account
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@legalmgmt.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'phone' => '555-0001',
            'address' => 'Admin Office',
        ]);

        // Create lawyer account
        User::create([
            'name' => 'John Lawyer',
            'email' => 'lawyer@legalmgmt.test',
            'password' => Hash::make('password123'),
            'role' => 'lawyer',
            'is_active' => true,
            'phone' => '555-0002',
            'address' => 'Law Office',
        ]);

        // Create client account
        User::create([
            'name' => 'Jane Client',
            'email' => 'client@legalmgmt.test',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'is_active' => true,
            'phone' => '555-0003',
            'address' => 'Home Address',
        ]);

       User::all()->each(function($user) {
    echo "  - {$user->email} (password: password123, role: {$user->role})\n";
});
    }
}
