<?php

namespace App\Policies;

use App\Models\Guardian;
use App\Models\User;

class GuardianPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar', 'guardian']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Guardian $guardian): bool
    {
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('guardian')) {
            // Ensure the guardian relationship is loaded for the user
            if (! $user->relationLoaded('guardian')) {
                $user->load('guardian');
            }

            return $guardian->id === $user->guardian?->id;
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
    public function update(User $user, Guardian $guardian): bool
    {
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        if ($user->hasRole('guardian')) {
            // Ensure the guardian relationship is loaded for the user
            if (! $user->relationLoaded('guardian')) {
                $user->load('guardian');
            }

            return $guardian->id === $user->guardian?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Guardian $guardian): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Guardian $guardian): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Guardian $guardian): bool
    {
        return $user->hasRole('super_admin');
    }
}
