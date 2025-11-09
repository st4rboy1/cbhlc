<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('guardian')) {
            return $invoice->enrollment->guardian_id === $user->guardian->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super_admin', 'registrar']);
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('guardian')) {
            return $invoice->enrollment->guardian_id === $user->guardian->id;
        }

        return false;
    }
}
