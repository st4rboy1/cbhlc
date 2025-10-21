<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registrar\BulkApproveEnrollmentsRequest;
use App\Http\Requests\Registrar\RejectEnrollmentRequest;
use App\Http\Requests\Registrar\UpdatePaymentStatusRequest;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of all enrollments.
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['student', 'guardian']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_year')) {
            $query->where('school_year', $request->school_year);
        }

        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->latest('created_at')->paginate(20);

        return Inertia::render('registrar/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['status', 'school_year', 'grade_level', 'payment_status', 'search']),
            'statuses' => EnrollmentStatus::values(),
            'paymentStatuses' => PaymentStatus::values(),
        ]);
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'guardian']);

        return Inertia::render('registrar/enrollments/show', [
            'enrollment' => $enrollment,
            'statuses' => EnrollmentStatus::values(),
            'paymentStatuses' => PaymentStatus::values(),
        ]);
    }

    /**
     * Approve an enrollment application.
     */
    public function approve(Request $request, Enrollment $enrollment)
    {
        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return back()->with('error', 'Only pending enrollments can be approved.');
        }

        // Use database transaction to ensure consistency
        \DB::transaction(function () use ($request, $enrollment) {
            // First, approve the enrollment
            $enrollment->update([
                'status' => EnrollmentStatus::APPROVED,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'remarks' => $request->input('remarks'),
            ]);

            // Generate invoice for the enrollment
            $invoiceService = new \App\Services\InvoiceService;
            $invoice = $invoiceService->createInvoiceFromEnrollment($enrollment);

            // Update enrollment with invoice reference and transition to ready for payment
            $enrollment->update([
                'status' => EnrollmentStatus::READY_FOR_PAYMENT,
                'invoice_id' => $invoice->id,
                'ready_for_payment_at' => now(),
            ]);

            // TODO: Send notification to guardian about approval and payment requirements
        });

        return back()->with('success', 'Enrollment approved successfully. Invoice has been generated and sent to the parent.');
    }

    /**
     * Reject an enrollment application.
     */
    public function reject(RejectEnrollmentRequest $request, Enrollment $enrollment)
    {
        $validated = $request->validated();

        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return back()->with('error', 'Only pending enrollments can be rejected.');
        }

        $enrollment->update([
            'status' => EnrollmentStatus::REJECTED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'remarks' => $validated['reason'],
        ]);

        return back()->with('success', 'Enrollment rejected.');
    }

    /**
     * Request more information from guardian.
     */
    public function requestInfo(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return back()->with('error', 'Only pending enrollments can have information requested.');
        }

        $enrollment->update([
            'info_requested' => true,
            'info_request_message' => $validated['message'],
            'info_request_date' => now(),
            'info_requested_by' => Auth::id(),
        ]);

        // Send email notification to guardian
        $enrollment->load(['student', 'guardian.user']);
        \Illuminate\Support\Facades\Mail::to($enrollment->guardian->user->email)
            ->send(new \App\Mail\EnrollmentInfoRequested($enrollment));

        return back()->with('success', 'Information request sent to guardian successfully.');
    }

    /**
     * Update enrollment payment status.
     */
    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, Enrollment $enrollment)
    {
        $validated = $request->validated();

        $enrollment->update([
            'amount_paid_cents' => $validated['amount_paid'],
            'payment_status' => PaymentStatus::from($validated['payment_status']),
            'balance_cents' => $enrollment->total_amount_cents - $validated['amount_paid'],
            'remarks' => $validated['remarks'] ?? $enrollment->remarks,
        ]);

        return back()->with('success', 'Payment status updated successfully.');
    }

    /**
     * Confirm payment for an enrollment.
     */
    public function confirmPayment(Request $request, Enrollment $enrollment)
    {
        if ($enrollment->status !== EnrollmentStatus::READY_FOR_PAYMENT) {
            return back()->with('error', 'Only enrollments ready for payment can be confirmed.');
        }

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:100',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        \DB::transaction(function () use ($validated, $enrollment) {
            // Calculate amounts in cents
            $amountPaidCents = (int) ($validated['amount_paid'] * 100);
            $balanceCents = $enrollment->net_amount_cents - $amountPaidCents;

            // Update enrollment payment details
            $enrollment->update([
                'status' => EnrollmentStatus::PAID,
                'payment_status' => $balanceCents <= 0 ? PaymentStatus::PAID : PaymentStatus::PARTIAL,
                'amount_paid_cents' => $amountPaidCents,
                'balance_cents' => max(0, $balanceCents),
                'payment_reference' => $validated['payment_reference'],
                'paid_at' => now(),
                'remarks' => $validated['notes'] ?? $enrollment->remarks,
            ]);

            // Update invoice status if fully paid
            if ($enrollment->invoice && $balanceCents <= 0) {
                $enrollment->invoice->update([
                    'status' => \App\Enums\InvoiceStatus::PAID,
                    'paid_at' => now(),
                ]);
            }

            // Auto-transition to ENROLLED if fully paid
            if ($balanceCents <= 0) {
                $enrollment->update([
                    'status' => EnrollmentStatus::ENROLLED,
                ]);
            }
        });

        // Refresh enrollment to get the latest status after transaction
        $enrollment->refresh();

        $message = $enrollment->status === EnrollmentStatus::ENROLLED
            ? 'Payment confirmed and student enrolled successfully.'
            : 'Payment confirmed. Partial payment recorded.';

        return back()->with('success', $message);
    }

    /**
     * Mark enrollment as completed.
     */
    public function complete(Enrollment $enrollment)
    {
        if ($enrollment->status !== EnrollmentStatus::ENROLLED) {
            return back()->with('error', 'Only enrolled students can be marked as completed.');
        }

        if ($enrollment->payment_status !== PaymentStatus::PAID) {
            return back()->with('error', 'Cannot complete enrollment with unpaid fees.');
        }

        $enrollment->update([
            'status' => EnrollmentStatus::COMPLETED,
        ]);

        return back()->with('success', 'Enrollment marked as completed.');
    }

    /**
     * Bulk approve enrollments.
     */
    public function bulkApprove(BulkApproveEnrollmentsRequest $request)
    {
        $validated = $request->validated();

        $count = Enrollment::whereIn('id', $validated['enrollment_ids'])
            ->where('status', EnrollmentStatus::PENDING)
            ->update([
                'status' => EnrollmentStatus::ENROLLED,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

        return back()->with('success', "{$count} enrollments approved successfully.");
    }

    /**
     * Export enrollments to Excel.
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Export functionality coming soon.');
    }
}
