<?php
namespace App\Policies;

use App\Models\User;
use App\Models\LegalCase;

class CasePolicy
{
    // Admins can do everything
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    // Lawyers see their own cases, clients see their own cases
    public function viewAny(User $user): bool
    {
        return $user->isLawyer() || $user->isClient();
    }

    public function view(User $user, LegalCase $case): bool
    {
        if ($user->isLawyer()) {
            return $user->id === $case->lawyer_id;
        }

        if ($user->isClient()) {
            return $user->id === $case->client_id;
        }

        return false;
    }

    // Only lawyers can create cases
    public function create(User $user): bool
    {
        return $user->isLawyer();
    }

    // Only the assigned lawyer can update
    public function update(User $user, LegalCase $case): bool
    {
        return $user->isLawyer() && $user->id === $case->lawyer_id;
    }

    // Only the assigned lawyer can delete
    public function delete(User $user, LegalCase $case): bool
    {
        return $user->isLawyer() && $user->id === $case->lawyer_id;
    }

    public function restore(User $user, LegalCase $case): bool
    {
        return $user->isLawyer() && $user->id === $case->lawyer_id;
    }

    public function forceDelete(User $user, LegalCase $case): bool
    {
        return false;
    }
}