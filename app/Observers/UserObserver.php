<?php
namespace App\Observers;

use App\Models\User;
use App\Services\AuditLogger;

class UserObserver
{
    public function created(User $user): void
    {
        AuditLogger::log('user_created', $user, [], [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }

    public function updated(User $user): void
    {
        // Don't log password changes in plain text
        $changes = collect($user->getChanges())
            ->except(['password', 'remember_token'])
            ->toArray();

        if (!empty($changes)) {
            AuditLogger::log(
                'user_updated',
                $user,
                collect($user->getOriginal())
                    ->except(['password', 'remember_token'])
                    ->toArray(),
                $changes
            );
        }
    }

    public function deleted(User $user): void
    {
        AuditLogger::log('user_deleted', $user, [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }
}