<?php

namespace App\Policies;

use App\Enums\VerificationStatus;
use App\Models\Document;
use App\Models\Student;
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
        // Super admins, administrators, and registrars can view all documents
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        // Guardians can view documents of students they are associated with
        if ($user->hasRole('guardian') && $user->guardian) {
            /** @var \App\Models\Student $student */
            $student = $document->student;

            return $student->guardians()
                ->where('guardians.id', $user->guardian->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can upload documents for a student.
     */
    public function uploadDocument(User $user, Student $student): bool
    {
        // Super admins, administrators, and registrars can upload documents for any student
        if ($user->hasAnyRole(['super_admin', 'administrator', 'registrar'])) {
            return true;
        }

        // Guardians can only upload documents for students they are associated with
        if ($user->hasRole('guardian') && $user->guardian) {
            return $student->guardians()
                ->where('guardians.id', $user->guardian->id)
                ->exists();
        }

        return false;
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

        // Guardians can delete their own documents, but only if pending or rejected
        if ($user->hasRole('guardian') && $user->guardian) {
            /** @var \App\Models\Student $student */
            $student = $document->student;

            return $student->guardians()
                ->where('guardians.id', $user->guardian->id)
                ->exists()
                && in_array($document->verification_status, [VerificationStatus::PENDING, VerificationStatus::REJECTED]);
        }

        return false;
    }

    /**
     * Determine whether the user can verify the document.
     */
    public function verify(User $user, Document $document): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar']);
    }

    /**
     * Determine whether the user can reject the document.
     */
    public function reject(User $user, Document $document): bool
    {
        return $user->hasAnyRole(['super_admin', 'administrator', 'registrar']);
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
