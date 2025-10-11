<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
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
    public function view(User $user, Student $student): bool
    {
        // Super admins, administrators, and registrars can view any student
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        // Guardians can view students they are associated with
        if ($user->hasRole('guardian') && $user->guardian) {
            return $student->guardians()->where('guardians.id', $user->guardian->id)->exists();
        }

        // Students can view their own record
        if ($user->hasRole('student') && $user->student) {
            return $user->student->id === $student->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar', 'guardian']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        // Super admins, administrators, and registrars can update any student
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        // Guardians can update students they are associated with
        if ($user->hasRole('guardian') && $user->guardian) {
            return $student->guardians()->where('guardians.id', $user->guardian->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Student $student): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Student $student): bool
    {
        return $user->hasRole('super_admin');
    }
}
