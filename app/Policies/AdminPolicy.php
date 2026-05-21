<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Determine if admin can message with a lawyer
     */
    public function adminMessage(User $admin, User $lawyer): bool
    {
        return $admin->role === 'admin' && $admin->is_active && $lawyer->role === 'lawyer';
    }

    /**
     * Only admins can access admin panel
     */
    public function accessAdminPanel(User $user): bool
    {
        return $user->role === 'admin' && $user->is_active;
    }

    /**
     * Only admins can manage users
     */
    public function manageUsers(User $user): bool
    {
        return $user->role === 'admin' && $user->is_active;
    }

    /**
     * Only admins can view audit logs
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->role === 'admin' && $user->is_active;
    }

    /**
     * Only admins can view reports
     */
    public function viewReports(User $user): bool
    {
        return $user->role === 'admin' && $user->is_active;
    }

    /**
     * Only admins can manage settings
     */
    public function manageSettings(User $user): bool
    {
        return $user->role === 'admin' && $user->is_active;
    }
}
