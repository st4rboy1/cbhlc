<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy
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
    public function view(User $user, Receipt $receipt): bool
    {
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('guardian')) {
            return $receipt->payment?->invoice?->enrollment?->guardian_id === $user->guardian?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Receipt $receipt): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Receipt $receipt): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Receipt $receipt): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Receipt $receipt): bool
    {
        return $user->hasRole('super_admin');
    }
}
