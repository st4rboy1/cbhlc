<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    /**
     * Display the tuition fees page
     */
    public function tuition(Request $request)
    {
        $user = $request->user();
        $enrollments = collect();

        // Get enrollments based on user role
        if ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            // Admin users can see all enrollments
            $enrollments = Enrollment::with(['student', 'user'])
                ->latest()
                ->paginate(10);
        } elseif ($user->hasRole('parent')) {
            // Parents can only see their children's enrollments
            $studentIds = $user->children()->pluck('students.id');
            $enrollments = Enrollment::with(['student', 'user'])
                ->whereIn('student_id', $studentIds)
                ->latest()
                ->paginate(10);
        }

        // Get configurable grade level fees for current school year
        $gradeLevelFees = GradeLevelFee::currentSchoolYear()
            ->active()
            ->get()
            ->mapWithKeys(function ($fee) {
                return [
                    $fee->grade_level->value => [
                        'tuition' => $fee->tuition_fee,
                        'miscellaneous' => $fee->miscellaneous_fee,
                        'laboratory' => $fee->laboratory_fee,
                        'library' => $fee->library_fee,
                        'sports' => $fee->sports_fee,
                        'total' => $fee->total_fee,
                    ]
                ];
            });

        return Inertia::render('tuition', [
            'enrollments' => $enrollments,
            'gradeLevelFees' => $gradeLevelFees,
        ]);
    }

    /**
     * Display the invoice for a specific enrollment
     */
    public function invoice(Request $request, $enrollmentId = null)
    {
        $user = $request->user();
        $enrollment = null;

        if ($enrollmentId) {
            $query = Enrollment::with(['student', 'user']);

            // Check permissions
            if ($user->hasRole(['super_admin', 'administrator', 'registrar'])) {
                // Admin users can see any invoice
                $enrollment = $query->find($enrollmentId);
            } elseif ($user->hasRole('parent')) {
                // Parents can only see their children's invoices
                $studentIds = $user->children()->pluck('students.id');
                $enrollment = $query->whereIn('student_id', $studentIds)
                    ->find($enrollmentId);
            }

            if (! $enrollment) {
                abort(404, 'Enrollment not found or you do not have permission to view this invoice.');
            }
        } else {
            // Get the latest enrollment for the user if no ID specified
            if ($user->hasRole('parent')) {
                $studentIds = $user->children()->pluck('students.id');
                $enrollment = Enrollment::with(['student', 'user'])
                    ->whereIn('student_id', $studentIds)
                    ->latest()
                    ->first();
            }
        }

        return Inertia::render('invoice', [
            'enrollment' => $enrollment,
            'invoiceNumber' => $enrollment?->enrollment_id ?? 'No Invoice Available',
            'currentDate' => now()->format('F d, Y'),
        ]);
    }

    /**
     * Update payment status for an enrollment
     */
    public function updatePayment(Request $request, $enrollmentId)
    {
        $user = $request->user();

        // Only admin users can update payment status
        if (! $user->hasRole(['super_admin', 'administrator', 'registrar'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_status' => ['required', 'in:'.implode(',', PaymentStatus::values())],
            'remarks' => 'nullable|string|max:500',
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        $enrollment->amount_paid = $validated['amount_paid'];
        $enrollment->payment_status = PaymentStatus::from($validated['payment_status']);
        $enrollment->balance = $enrollment->net_amount - $validated['amount_paid'];

        if ($validated['remarks']) {
            $enrollment->remarks = $validated['remarks'];
        }

        $enrollment->save();

        return redirect()->back()->with('success', 'Payment status updated successfully.');
    }
}
