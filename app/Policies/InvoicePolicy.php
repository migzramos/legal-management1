<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;

class InvoicePolicy
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
        return $user->isLawyer() || $user->isClient();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isLawyer()) {
            return $user->id === $invoice->lawyer_id;
        }

        if ($user->isClient()) {
            return $user->id === $invoice->client_id;
        }

        return false;
    }

    // Only lawyers can create invoices
    public function create(User $user): bool
    {
        return $user->isLawyer();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isLawyer() && $user->id === $invoice->lawyer_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isLawyer()
            && $user->id === $invoice->lawyer_id
            && $invoice->status === 'draft';
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->isLawyer() && $user->id === $invoice->lawyer_id;
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}