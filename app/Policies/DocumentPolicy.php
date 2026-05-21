<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Document;

class DocumentPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        // Lawyer assigned to the case can view
        if ($user->isLawyer()) {
            return $user->id === $document->case->lawyer_id;
        }

        // Client can only view documents marked visible
        if ($user->isClient()) {
            return $user->id === $document->case->client_id
                && $document->is_visible_to_client;
        }

        return false;
    }

    // Only lawyers can upload documents
    public function create(User $user): bool
    {
        return $user->isLawyer() || $user->isClient();
    }

    public function update(User $user, Document $document): bool
    {
        return $user->isLawyer() && $user->id === $document->case->lawyer_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->isLawyer() && $user->id === $document->case->lawyer_id;
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->isLawyer() && $user->id === $document->case->lawyer_id;
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}