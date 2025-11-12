<?php

namespace App\Policies;

use App\Models\EnrollmentPeriod;
use App\Models\User;

class EnrollmentPeriodPolicy
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
    public function view(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar']);
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
    public function update(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can activate the model.
     */
    public function activate(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can close the model.
     */
    public function close(User $user, EnrollmentPeriod $enrollmentPeriod): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }
}
