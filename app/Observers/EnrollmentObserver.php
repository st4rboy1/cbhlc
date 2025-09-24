<?php

namespace App\Observers;

use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Mail\EnrollmentSubmitted;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnrollmentObserver
{
    /**
     * Handle the Enrollment "creating" event.
     */
    public function creating(Enrollment $enrollment): void
    {
        // Generate enrollment ID if not provided
        if (empty($enrollment->enrollment_id)) {
            $enrollment->enrollment_id = $this->generateEnrollmentId();
        }

        // Set default status if not provided
        if (empty($enrollment->status)) {
            $enrollment->status = 'pending';
        }

        // Calculate fees if not set
        if (empty($enrollment->tuition_fee)) {
            $this->calculateFees($enrollment);
        }

        // Set payment status
        if (empty($enrollment->payment_status)) {
            $enrollment->payment_status = 'pending';
        }
    }

    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment): void
    {
        // Send enrollment submitted email
        if ($enrollment->guardian && !empty($enrollment->guardian->email)) {
            Mail::to($enrollment->guardian->email)
                ->queue(new EnrollmentSubmitted($enrollment));
        }

        // Log enrollment creation
        activity()
            ->performedOn($enrollment)
            ->causedBy(auth()->user())
            ->log('Enrollment created for student: ' . $enrollment->student->full_name);
    }

    /**
     * Handle the Enrollment "updating" event.
     */
    public function updating(Enrollment $enrollment): void
    {
        // Track status changes for email notifications
        if ($enrollment->isDirty('status')) {
            $oldStatus = $enrollment->getOriginal('status');
            $newStatus = $enrollment->status;

            // Set status change timestamps
            if ($oldStatus === 'pending' && $newStatus === 'approved') {
                $enrollment->approved_at = now();
                $enrollment->approved_by = auth()->id();
            } elseif ($oldStatus === 'pending' && $newStatus === 'rejected') {
                $enrollment->rejected_at = now();
                $enrollment->rejected_by = auth()->id();
            } elseif ($newStatus === 'enrolled') {
                $enrollment->enrolled_at = now();
            }
        }
    }

    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment): void
    {
        // Send email notifications based on status change
        if ($enrollment->wasChanged('status')) {
            $this->sendStatusChangeEmail($enrollment);
        }

        // Update student's grade level if enrollment is approved
        if ($enrollment->wasChanged('status') && $enrollment->status === 'approved') {
            $enrollment->student->update([
                'grade_level' => $enrollment->grade_level,
            ]);
        }

        // Log significant changes
        if ($enrollment->wasChanged(['status', 'payment_status', 'grade_level'])) {
            activity()
                ->performedOn($enrollment)
                ->causedBy(auth()->user())
                ->withProperties(['changes' => $enrollment->getChanges()])
                ->log('Enrollment updated for student: ' . $enrollment->student->full_name);
        }
    }

    /**
     * Handle the Enrollment "deleted" event.
     */
    public function deleted(Enrollment $enrollment): void
    {
        activity()
            ->performedOn($enrollment)
            ->causedBy(auth()->user())
            ->log('Enrollment deleted for student: ' . $enrollment->student->full_name);
    }

    /**
     * Generate a unique enrollment ID.
     */
    private function generateEnrollmentId(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Get the latest enrollment for this month
        $latestEnrollment = Enrollment::where('enrollment_id', 'like', "ENR-{$year}{$month}%")
            ->orderBy('enrollment_id', 'desc')
            ->first();

        if ($latestEnrollment) {
            // Extract the sequence number and increment
            $sequence = intval(substr($latestEnrollment->enrollment_id, -4)) + 1;
        } else {
            // Start with 1 if no enrollments for this month
            $sequence = 1;
        }

        return sprintf('ENR-%s%s%04d', $year, $month, $sequence);
    }

    /**
     * Calculate enrollment fees.
     */
    private function calculateFees(Enrollment $enrollment): void
    {
        $fees = GradeLevelFee::getFeesForGrade(
            $enrollment->grade_level,
            $enrollment->school_year
        );

        if ($fees) {
            $enrollment->tuition_fee = $fees->tuition_fee;
            $enrollment->miscellaneous_fee = $fees->miscellaneous_fee ?? 0;

            // Only set other_fees if the field exists on the enrollment model
            if (array_key_exists('other_fees', $enrollment->getAttributes())) {
                $enrollment->other_fees = 0;
            }

            $enrollment->total_amount = $fees->total_fee;
            $enrollment->balance_cents = $fees->total_fee * 100;
        } else {
            // Set default values if no fee structure found
            $enrollment->tuition_fee = 0;
            $enrollment->miscellaneous_fee = 0;

            // Only set other_fees if the field exists
            if (array_key_exists('other_fees', $enrollment->getAttributes())) {
                $enrollment->other_fees = 0;
            }

            $enrollment->total_amount = 0;
            $enrollment->balance_cents = 0;
        }
    }

    /**
     * Send status change email notifications.
     */
    private function sendStatusChangeEmail(Enrollment $enrollment): void
    {
        if (!$enrollment->guardian || empty($enrollment->guardian->email)) {
            return;
        }

        $newStatus = $enrollment->status;
        $email = $enrollment->guardian->email;

        switch ($newStatus) {
            case 'approved':
                Mail::to($email)->queue(new EnrollmentApproved($enrollment));
                break;
            case 'rejected':
                $reason = $enrollment->rejection_reason ?? 'No specific reason provided';
                Mail::to($email)->queue(new EnrollmentRejected($enrollment, $reason));
                break;
        }
    }
}