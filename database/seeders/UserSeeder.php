<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin - Password: password123
        User::updateOrCreate(
            ['email' => 'admin@legal.com'],
            [
                'name'      => 'System Admin',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'phone'     => '09001112222',
                'is_active' => true,
            ]
        );

        // Lawyers - Password: password123
        User::updateOrCreate(
            ['email' => 'lawyer@legal.com'],
            [
                'name'      => 'Atty. Juan Dela Cruz',
                'password'  => Hash::make('password123'),
                'role'      => 'lawyer',
                'phone'     => '09111234567',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'lawyer2@legal.com'],
            [
                'name'      => 'Atty. Maria Santos',
                'password'  => Hash::make('password123'),
                'role'      => 'lawyer',
                'phone'     => '09222345678',
                'is_active' => true,
            ]
        );

        // Clients - Password: password123
        User::updateOrCreate(
            ['email' => 'client@legal.com'],
            [
                'name'      => 'Pedro Reyes',
                'password'  => Hash::make('password123'),
                'role'      => 'client',
                'phone'     => '09333456789',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'client2@legal.com'],
            [
                'name'      => 'Ana Gonzales',
                'password'  => Hash::make('password123'),
                'role'      => 'client',
                'phone'     => '09444567890',
                'is_active' => true,
            ]
        );
    }
}