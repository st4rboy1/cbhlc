<?php

namespace App\Contracts\Services;

use App\Models\Enrollment;
use Illuminate\Support\Collection;

interface BillingServiceInterface
{
    /**
     * Get billing information for an enrollment
     */
    public function getBillingDetails(Enrollment $enrollment): array;

    /**
     * Get billing summary for guardian's students
     */
    public function getGuardianBillingSummary(int $guardianId): \Illuminate\Support\Collection;

    /**
     * Calculate payment plan
     */
    public function calculatePaymentPlan(float $totalAmount, string $plan): array;

    /**
     * Process payment
     */
    public function processPayment(Enrollment $enrollment, float $amount, array $paymentDetails = []): array;

    /**
     * Generate invoice
     */
    public function generateInvoice(Enrollment $enrollment): array;

    /**
     * Get payment history
     */
    public function getPaymentHistory(Enrollment $enrollment): \Illuminate\Support\Collection;

    /**
     * Calculate late fees
     */
    public function calculateLateFees(Enrollment $enrollment): float;

    /**
     * Apply discount
     */
    public function applyDiscount(Enrollment $enrollment, string $discountType, float $discountValue): Enrollment;

    /**
     * Get fee structure by grade level
     */
    public function getFeeStructure(string $gradeLevel): array;
}
