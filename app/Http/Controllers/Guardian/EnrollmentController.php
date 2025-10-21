<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\StoreEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GuardianStudent;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of guardian's children enrollments.
     */
    public function index(Request $request)
    {
        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        // Get student IDs for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $guardian->id)
            ->pluck('student_id');

        // Build query with filters
        $query = Enrollment::with(['student', 'guardian'])
            ->whereIn('student_id', $studentIds);

        // Filter by school year
        if ($request->filled('school_year')) {
            $query->where('school_year', $request->school_year);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by student name or enrollment ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('enrollment_id', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $enrollments = $query->paginate(10)->withQueryString();

        // Get filter options
        $students = Student::whereIn('id', $studentIds)
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->map(fn ($student) => [
                'value' => (string) $student->id,
                'label' => "{$student->first_name} {$student->last_name}",
            ]);

        $schoolYears = Enrollment::whereIn('student_id', $studentIds)
            ->select('school_year')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year')
            ->map(fn ($year) => [
                'value' => $year,
                'label' => $year,
            ]);

        $statuses = collect(EnrollmentStatus::cases())
            ->map(fn ($status) => [
                'value' => $status->value,
                'label' => ucfirst($status->value),
            ]);

        return Inertia::render('guardian/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => [
                'school_year' => $request->school_year,
                'student_id' => $request->student_id,
                'status' => $request->status,
                'search' => $request->search,
            ],
            'filterOptions' => [
                'students' => $students,
                'schoolYears' => $schoolYears,
                'statuses' => $statuses,
            ],
        ]);
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create(Request $request)
    {
        // Check for active enrollment period
        $activePeriod = EnrollmentPeriod::active()->first();

        if (! $activePeriod) {
            return back()->withErrors([
                'enrollment' => 'Enrollment is currently closed. No active enrollment period available.',
            ]);
        }

        if (! $activePeriod->isOpen()) {
            return back()->withErrors([
                'enrollment' => 'Enrollment period is not currently open. The deadline has passed.',
            ]);
        }

        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();
        $currentSchoolYear = $activePeriod->school_year;
        $selectedStudentId = $request->query('student_id');

        // Get guardian's students with enrollment info
        $studentIds = GuardianStudent::where('guardian_id', $guardian->id)
            ->pluck('student_id');

        // Get students who have pending or active enrollments
        $studentsWithEnrollments = Enrollment::whereIn('student_id', $studentIds)
            ->whereIn('status', [EnrollmentStatus::PENDING, EnrollmentStatus::ENROLLED])
            ->pluck('student_id');

        // Filter out students with pending or active enrollments
        $eligibleStudentIds = $studentIds->diff($studentsWithEnrollments);

        $studentsQuery = Student::whereIn('id', $eligibleStudentIds)->get();

        $students = $studentsQuery->map(function ($student) use ($currentSchoolYear) {
            return [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'student_id' => $student->student_id,
                'is_new_student' => $student->isNewStudent(),
                'current_grade_level' => $student->getCurrentGradeLevel()?->value,
                'available_grade_levels' => array_map(
                    fn ($grade) => $grade->value,
                    $student->getAvailableGradeLevels($currentSchoolYear)
                ),
            ];
        });

        return Inertia::render('guardian/enrollments/create', [
            'students' => $students,
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
            'currentSchoolYear' => $currentSchoolYear,
            'selectedStudentId' => $selectedStudentId,
            'activePeriod' => $activePeriod,
            'daysRemaining' => $activePeriod->getDaysRemaining(),
        ]);
    }

    /**
     * Store a newly created enrollment in storage.
     */
    public function store(StoreEnrollmentRequest $request)
    {
        // Check for active enrollment period
        $activePeriod = EnrollmentPeriod::active()->first();

        if (! $activePeriod) {
            return back()->withErrors([
                'enrollment' => 'Enrollment is currently closed. No active enrollment period available.',
            ])->withInput();
        }

        if (! $activePeriod->isOpen()) {
            return back()->withErrors([
                'enrollment' => 'Enrollment period is not currently open. The deadline has passed.',
            ])->withInput();
        }

        $validated = $request->validated();

        $student = Student::findOrFail($validated['student_id']);

        // Validate student eligibility for this enrollment period
        $eligibilityErrors = Enrollment::canEnrollForPeriod($activePeriod, $student);

        if (! empty($eligibilityErrors)) {
            return back()->withErrors([
                'enrollment' => $eligibilityErrors[0],
            ])->withInput();
        }

        // Check if student is an existing student (has previous enrollments)
        $previousEnrollments = Enrollment::where('student_id', $validated['student_id'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($previousEnrollments) {
            // Business Rule 1: Existing students must enroll in First quarter
            $validated['quarter'] = Quarter::FIRST->value;
        }

        // Get the fee for the selected grade level and school year
        $gradeLevelFee = \App\Models\GradeLevelFee::where('grade_level', $validated['grade_level'])
            ->where('school_year', $activePeriod->school_year)
            ->first();

        $tuitionFeeCents = ($gradeLevelFee ? $gradeLevelFee->tuition_fee : 0) * 100;
        $miscFeeCents = ($gradeLevelFee ? $gradeLevelFee->miscellaneous_fee : 0) * 100;
        $laboratoryFeeCents = 0;
        $libraryFeeCents = 0;
        $sportsFeeCents = 0;
        $discountCents = 0;

        // Calculate totals
        $totalAmountCents = $tuitionFeeCents + $miscFeeCents + $laboratoryFeeCents + $libraryFeeCents + $sportsFeeCents;
        $netAmountCents = $totalAmountCents - $discountCents;
        $amountPaidCents = 0;
        $balanceCents = $netAmountCents - $amountPaidCents;

        // Get Guardian model ID for the authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'guardian_id' => $guardian->id,
            'school_year' => $activePeriod->school_year,
            'enrollment_period_id' => $activePeriod->id,
            'quarter' => Quarter::from($validated['quarter']),
            'grade_level' => GradeLevel::from($validated['grade_level']),
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => $tuitionFeeCents,
            'miscellaneous_fee_cents' => $miscFeeCents,
            'laboratory_fee_cents' => $laboratoryFeeCents,
            'library_fee_cents' => $libraryFeeCents,
            'sports_fee_cents' => $sportsFeeCents,
            'total_amount_cents' => $totalAmountCents,
            'discount_cents' => $discountCents,
            'net_amount_cents' => $netAmountCents,
            'payment_status' => PaymentStatus::PENDING,
            'amount_paid_cents' => $amountPaidCents,
            'balance_cents' => $balanceCents,
        ]);

        return redirect()->route('guardian.enrollments.index')
            ->with('success', 'Enrollment application submitted successfully. Please wait for approval.');
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to view this enrollment.');
        }

        $enrollment->load(['student', 'guardian']);

        return Inertia::render('guardian/enrollments/show', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(Enrollment $enrollment)
    {
        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to edit this enrollment.');
        }

        // Only allow editing pending enrollments
        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return redirect()->route('guardian.enrollments.show', $enrollment->id)
                ->with('error', 'Only pending enrollments can be edited.');
        }

        $enrollment->load(['student']);

        return Inertia::render('guardian/enrollments/edit', [
            'enrollment' => $enrollment,
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
        ]);
    }

    /**
     * Update the specified enrollment in storage.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to update this enrollment.');
        }

        // Only allow updating pending enrollments
        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return redirect()->route('guardian.enrollments.show', $enrollment->id)
                ->with('error', 'Only pending enrollments can be updated.');
        }

        $validated = $request->validate([
            'quarter' => 'required|string',
            'grade_level' => 'required|string',
        ]);

        $enrollment->update([
            'quarter' => Quarter::from($validated['quarter']),
            'grade_level' => GradeLevel::from($validated['grade_level']),
        ]);

        return redirect()->route('guardian.enrollments.show', $enrollment->id)
            ->with('success', 'Enrollment application updated successfully.');
    }

    /**
     * Cancel the specified enrollment.
     */
    public function destroy(Enrollment $enrollment)
    {
        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to cancel this enrollment.');
        }

        // Only allow canceling pending enrollments
        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return redirect()->route('guardian.enrollments.show', $enrollment->id)
                ->with('error', 'Only pending enrollments can be canceled.');
        }

        $enrollment->delete();

        return redirect()->route('guardian.enrollments.index')
            ->with('success', 'Enrollment application canceled successfully.');
    }

    /**
     * Download payment history report PDF
     */
    public function downloadPaymentHistory(Enrollment $enrollment)
    {
        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(404);
        }

        $enrollment->load('student');

        // Get all payments for this enrollment via invoice
        $payments = Payment::where('invoice_id', $enrollment->id)
            ->orderBy('payment_date', 'asc')
            ->get();

        $pdf = Pdf::loadView('pdf.payment-history', [
            'enrollment' => $enrollment,
            'payments' => $payments,
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->download("payment-history-{$enrollment->enrollment_id}.pdf");
    }
}
