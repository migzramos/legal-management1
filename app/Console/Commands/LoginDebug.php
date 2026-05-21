<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class LoginDebug extends Command
{
    protected $signature = 'auth:login-debug {email} {password}';
    protected $description = 'Debug login attempt with specific credentials';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info('=== LOGIN DEBUG ===');
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->newLine();

        // Step 1: User exists?
        $this->info('Step 1: Check user exists');
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->line("  ✅ User found: {$user->name} (ID: {$user->id})");
        } else {
            $this->error("  ❌ User NOT found");
            return 1;
        }

        // Step 2: Is active?
        $this->info('Step 2: Check user is active');
        if ($user->is_active) {
            $this->line("  ✅ User is active");
        } else {
            $this->error("  ❌ User is INACTIVE");
            return 1;
        }

        // Step 3: Password verify
        $this->info('Step 3: Verify password hash');
        $isValid = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        if ($isValid) {
            $this->line("  ✅ Password hash verification: PASS");
        } else {
            $this->error("  ❌ Password hash verification: FAIL");
            $this->line("     Expected: {$password}");
            $this->line("     Hash: " . substr($user->password, 0, 40) . "...");
            return 1;
        }

        // Step 4: Auth::attempt
        $this->info('Step 4: Auth::attempt()');
        $attempt = Auth::attempt(['email' => $email, 'password' => $password]);
        if ($attempt) {
            $this->line("  ✅ Auth::attempt: SUCCESS");
            $authUser = Auth::user();
            $this->line("  ✅ Authenticated as: {$authUser->email}");
            Auth::logout();
        } else {
            $this->error("  ❌ Auth::attempt: FAILED");
            return 1;
        }

        $this->newLine();
        $this->info('✅ LOGIN DEBUG COMPLETE - All checks passed');
        return 0;
    }
}
