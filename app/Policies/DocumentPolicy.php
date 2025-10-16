<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar', 'guardian']);
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // Super admins, administrators, and registrars can view any document
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        // Guardians can view documents of students they are associated with
        if ($user->hasRole('guardian') && $user->guardian) {
            /** @var \App\Models\Student $student */
            $student = $document->student;

            return $student->guardians()->where('guardians.id', $user->guardian->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar', 'guardian']);
    }

    /**
     * Determine whether the user can verify the document.
     */
    public function verify(User $user, Document $document): bool
    {
        // Only registrars, administrators, and super admins can verify documents
        if (! $user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return false;
        }

        // Cannot verify already verified documents
        if ($document->isVerified()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can reject the document.
     */
    public function reject(User $user, Document $document): bool
    {
        // Only registrars, administrators, and super admins can reject documents
        if (! $user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return false;
        }

        // Cannot reject already verified documents
        if ($document->isVerified()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // Super admins and administrators can delete any document
        if ($user->hasAnyRole(['super_admin', 'administrator'])) {
            return true;
        }

        // Guardians can delete their own pending or rejected documents
        if ($user->hasRole('guardian') && $user->guardian) {
            /** @var \App\Models\Student $student */
            $student = $document->student;

            $isStudentGuardian = $student->guardians()
                ->where('guardians.id', $user->guardian->id)
                ->exists();

            return $isStudentGuardian && ! $document->isVerified();
        }

        return false;
    }
}
