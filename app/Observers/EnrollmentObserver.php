<?php

namespace App\Observers;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\EnrollmentSubmitted;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use Illuminate\Support\Facades\Mail;

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
            $enrollment->status = EnrollmentStatus::PENDING;
        }

        // Calculate fees if not set
        if (empty($enrollment->tuition_fee)) {
            $this->calculateFees($enrollment);
        }

        // Set payment status
        if (empty($enrollment->payment_status)) {
            $enrollment->payment_status = PaymentStatus::PENDING;
        }
    }

    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment): void
    {
        // Send enrollment submitted email
        if ($enrollment->guardian && $enrollment->guardian->user && ! empty($enrollment->guardian->user->email)) {
            Mail::to($enrollment->guardian->user->email)
                ->queue(new EnrollmentSubmitted($enrollment));
        }

        // Note: Activity logging is handled automatically by LogsActivity trait
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
            if ($oldStatus->value === 'pending' && ($newStatus->value === 'approved' || $newStatus->value === 'enrolled')) {
                $enrollment->approved_at = now();
                $enrollment->approved_by = auth()->id();
            } elseif ($oldStatus->value === 'pending' && $newStatus->value === 'rejected') {
                $enrollment->rejected_at = now();
            }
        }
    }

    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment): void
    {
        // Update student's grade level if enrollment is approved or enrolled
        if ($enrollment->wasChanged('status') && ($enrollment->status->value === 'approved' || $enrollment->status->value === 'enrolled')) {
            $enrollment->student->update([
                'grade_level' => $enrollment->grade_level,
            ]);
        }

        // Note: Activity logging is handled automatically by LogsActivity trait
    }

    /**
     * Handle the Enrollment "deleted" event.
     */
    public function deleted(Enrollment $enrollment): void
    {
        // Note: Activity logging is handled automatically by LogsActivity trait
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
            $enrollment->enrollment_period_id
        );

        if ($fees) {
            $enrollment->tuition_fee = $fees->tuition_fee;
            $enrollment->miscellaneous_fee = $fees->miscellaneous_fee ?? 0;

            // Calculate total from the fees we're actually using
            $total = $enrollment->tuition_fee + $enrollment->miscellaneous_fee;

            $enrollment->total_amount = $total;
            $enrollment->net_amount = $total - ($enrollment->discount ?? 0);
            $enrollment->balance = $enrollment->net_amount;
        } else {
            // Set default values if no fee structure found
            $enrollment->tuition_fee = 0;
            $enrollment->miscellaneous_fee = 0;
            $enrollment->total_amount = 0;
            $enrollment->net_amount = 0;
            $enrollment->balance = 0;
        }
    }
}
