<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
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

        return Inertia::render('enrollments/index', [
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

        return Inertia::render('enrollments/show', [
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

        $enrollment->update([
            'status' => EnrollmentStatus::ENROLLED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'remarks' => $request->input('remarks'),
        ]);

        return back()->with('success', 'Enrollment approved successfully.');
    }

    /**
     * Reject an enrollment application.
     */
    public function reject(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

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
     * Update enrollment payment status.
     */
    public function updatePaymentStatus(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'payment_status' => 'required|string|in:'.implode(',', PaymentStatus::values()),
            'remarks' => 'nullable|string',
        ]);

        $enrollment->update([
            'payment_status' => PaymentStatus::from($validated['payment_status']),
            'remarks' => $validated['remarks'] ?? $enrollment->remarks,
        ]);

        return back()->with('success', 'Payment status updated successfully.');
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
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id',
        ]);

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
