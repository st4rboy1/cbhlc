<?php

namespace App\Contracts\Services;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BillingServiceInterface
{
    /**
     * Get paginated invoices with filters
     */
    public function getPaginatedInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find invoice with payment history
     */
    public function findWithPayments(int $invoiceId): Invoice;

    /**
     * Generate invoice for enrollment
     */
    public function generateInvoice(Enrollment $enrollment): Invoice;

    /**
     * Record payment for invoice
     */
    public function recordPayment(Invoice $invoice, array $data): Payment;

    /**
     * Calculate payment plan
     */
    public function calculatePaymentPlan(float $totalAmount, string $plan): array;

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(): Collection;

    /**
     * Get payments by enrollment
     */
    public function getPaymentsByEnrollment(int $enrollmentId): Collection;

    /**
     * Get billing statistics
     */
    public function getStatistics(?string $fromDate = null, ?string $toDate = null): array;

    /**
     * Format invoice for display
     */
    public function formatInvoiceForDisplay(Invoice $invoice): array;

    /**
     * Get billing information for an enrollment
     */
    public function getBillingDetails(Enrollment $enrollment): array;

    /**
     * Get available payment plans
     */
    public function getPaymentPlans(float $totalAmount): array;

    /**
     * Process payment
     */
    public function processPayment(Enrollment $enrollment, float $amount, array $paymentDetails = []): array;

    /**
     * Get guardian billing summary
     */
    public function getGuardianBillingSummary(int $guardianId): Collection;

    /**
     * Get payment history
     */
    public function getPaymentHistory(Enrollment $enrollment): Collection;

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
