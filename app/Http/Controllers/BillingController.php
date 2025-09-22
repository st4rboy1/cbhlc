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
            $enrollments = Enrollment::with(['student', 'guardian'])
                ->latest()
                ->paginate(10);
        } elseif ($user->hasRole('guardian')) {
            // Guardians can only see their children's enrollments
            $guardian = \App\Models\Guardian::where('user_id', $user->id)->first();
            if ($guardian) {
                $studentIds = $guardian->children()->pluck('students.id');
                $enrollments = Enrollment::with(['student', 'guardian'])
                    ->whereIn('student_id', $studentIds)
                    ->latest()
                    ->paginate(10);
            }
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
                    ],
                ];
            });

        return Inertia::render('tuition', [
            'enrollments' => $enrollments,
            'gradeLevelFees' => $gradeLevelFees,
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

        $enrollment->amount_paid_cents = $validated['amount_paid'];
        $enrollment->payment_status = PaymentStatus::from($validated['payment_status']);
        $enrollment->balance_cents = $enrollment->net_amount_cents - $validated['amount_paid'];

        if (isset($validated['remarks']) && $validated['remarks']) {
            $enrollment->remarks = $validated['remarks'];
        }

        $enrollment->save();

        return redirect()->back()->with('success', 'Payment status updated successfully.');
    }
}
