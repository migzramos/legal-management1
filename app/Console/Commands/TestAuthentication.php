<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestAuthentication extends Command
{
    protected $signature = 'auth:test';
    protected $description = 'Test authentication system';

    public function handle()
    {
        $this->info('=== AUTHENTICATION SYSTEM TEST ===');
        $this->newLine();

        // Test 1: User exists
        $this->info('Test 1: User Records');
        $users = User::all();
        $this->info("  Total users: {$users->count()}");
        if ($users->count() > 0) {
            $this->line('  ✅ Users exist');
        } else {
            $this->error('  ❌ No users in database');
            return 1;
        }

        // Test 2: Direct password verification
        $this->info('Test 2: Password Verification');
        $user = User::where('email', 'admin@legal.com')->first();
        if (!$user) {
            $this->error('  ❌ admin@legal.com not found');
            return 1;
        }

        $isValid = Hash::check('password123', $user->password);
        if ($isValid) {
            $this->line('  ✅ Hash::check(password123) = PASS');
        } else {
            $this->error('  ❌ Hash::check(password123) = FAIL');
            return 1;
        }

        // Test 3: Auth::attempt
        $this->info('Test 3: Auth::attempt()');
        $attempt = Auth::attempt(['email' => 'admin@legal.com', 'password' => 'password123']);
        if ($attempt) {
            $this->line('  ✅ Auth::attempt = SUCCESS');
            $authUser = Auth::user();
            $this->line('  ✅ Authenticated as: ' . $authUser->email . ' (role: ' . $authUser->role . ')');
            Auth::logout();
        } else {
            $this->error('  ❌ Auth::attempt = FAILED');
            return 1;
        }

        // Test 4: All users
        $this->info('Test 4: All Test Accounts');
        foreach ($users as $u) {
            $active = $u->is_active ? '✅' : '❌';
            $this->line("  {$active} {$u->email} (role: {$u->role})");
        }

        // Test 5: Wrong password
        $this->info('Test 5: Auth with Wrong Password');
        $wrongAttempt = Auth::attempt(['email' => 'admin@legal.com', 'password' => 'wrongpassword']);
        if (!$wrongAttempt) {
            $this->line('  ✅ Auth::attempt (wrong password) = FAIL (correct behavior)');
        } else {
            $this->error('  ❌ Auth::attempt (wrong password) = SUCCESS (should have failed!)');
            return 1;
        }

        $this->newLine();
        $this->info('=== ALL TESTS PASSED ✅ ===');
        $this->line('Authentication system is operational.');
        $this->line('');
        $this->line('Test Accounts:');
        $this->table(['Email', 'Password', 'Role'], [
            ['admin@legal.com', 'password123', 'admin'],
            ['lawyer@legal.com', 'password123', 'lawyer'],
            ['lawyer2@legal.com', 'password123', 'lawyer'],
            ['client@legal.com', 'password123', 'client'],
            ['client2@legal.com', 'password123', 'client'],
        ]);

        return 0;
    }
}
