<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
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
            $enrollments = Enrollment::with(['student', 'user'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
        }

        // Calculate grade level fees (static for now)
        $gradeLevelFees = [
            'Nursery' => ['tuition' => 15000, 'miscellaneous' => 2000],
            'Kinder 1' => ['tuition' => 16000, 'miscellaneous' => 2200],
            'Kinder 2' => ['tuition' => 16000, 'miscellaneous' => 2200],
            'Grade 1' => ['tuition' => 18000, 'miscellaneous' => 2500],
            'Grade 2' => ['tuition' => 18000, 'miscellaneous' => 2500],
            'Grade 3' => ['tuition' => 18000, 'miscellaneous' => 2500],
            'Grade 4' => ['tuition' => 19000, 'miscellaneous' => 2800],
            'Grade 5' => ['tuition' => 19000, 'miscellaneous' => 2800],
            'Grade 6' => ['tuition' => 19000, 'miscellaneous' => 2800],
            'Grade 7' => ['tuition' => 22000, 'miscellaneous' => 3000],
            'Grade 8' => ['tuition' => 22000, 'miscellaneous' => 3000],
            'Grade 9' => ['tuition' => 22000, 'miscellaneous' => 3000],
            'Grade 10' => ['tuition' => 22000, 'miscellaneous' => 3000],
            'Grade 11' => ['tuition' => 25000, 'miscellaneous' => 3500],
            'Grade 12' => ['tuition' => 25000, 'miscellaneous' => 3500],
        ];

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
                $enrollment = $query->where('user_id', $user->id)
                    ->find($enrollmentId);
            }

            if (! $enrollment) {
                abort(404, 'Enrollment not found or you do not have permission to view this invoice.');
            }
        } else {
            // Get the latest enrollment for the user if no ID specified
            if ($user->hasRole('parent')) {
                $enrollment = Enrollment::with(['student', 'user'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first();
            }
        }

        // If no enrollment found, create sample data for display purposes
        if (! $enrollment) {
            $enrollment = $this->createSampleEnrollment();
        }

        return Inertia::render('invoice', [
            'enrollment' => $enrollment,
            'invoiceNumber' => $enrollment->enrollment_id ?? 'INV-2025-001',
            'currentDate' => now()->format('F d, Y'),
        ]);
    }

    /**
     * Create sample enrollment data for display purposes
     */
    private function createSampleEnrollment()
    {
        return (object) [
            'enrollment_id' => 'ENR-'.date('Y').'-'.str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
            'student' => (object) [
                'first_name' => 'Sample',
                'last_name' => 'Student',
                'middle_name' => 'Middle',
                'student_id' => 'STU-'.date('Y').'-001',
                'grade_level' => 'Grade 7',
                'section' => 'Section A',
            ],
            'school_year' => date('Y').'-'.(date('Y') + 1),
            'semester' => 'First',
            'tuition_fee' => 22000,
            'miscellaneous_fee' => 3000,
            'laboratory_fee' => 500,
            'library_fee' => 300,
            'sports_fee' => 200,
            'total_amount' => 26000,
            'discount' => 0,
            'net_amount' => 26000,
            'amount_paid' => 0,
            'balance' => 26000,
            'payment_status' => 'pending',
            'payment_due_date' => now()->addMonth()->format('Y-m-d'),
            'created_at' => now(),
        ];
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
            'payment_status' => 'required|in:pending,partial,paid',
            'remarks' => 'nullable|string|max:500',
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        $enrollment->amount_paid = $validated['amount_paid'];
        $enrollment->payment_status = $validated['payment_status'];
        $enrollment->balance = $enrollment->net_amount - $validated['amount_paid'];

        if ($validated['remarks']) {
            $enrollment->remarks = $validated['remarks'];
        }

        $enrollment->save();

        return redirect()->back()->with('success', 'Payment status updated successfully.');
    }
}
