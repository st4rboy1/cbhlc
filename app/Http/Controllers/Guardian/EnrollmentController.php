<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\StoreEnrollmentRequest;
use App\Http\Requests\Guardian\UpdateEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\EnrollmentPeriod;
use App\Models\GuardianStudent;
use App\Models\Payment;
use App\Models\SchoolInformation;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
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
        $query = Enrollment::with(['student', 'guardian', 'schoolYear'])
            ->whereIn('student_id', $studentIds);

        // Filter by school year
        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->school_year_id);
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

        $schoolYears = SchoolYear::orderBy('start_year', 'desc')->get();

        $statuses = collect(EnrollmentStatus::cases())
            ->map(fn ($status) => [
                'value' => $status->value,
                'label' => ucfirst($status->value),
            ]);

        return Inertia::render('guardian/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => [
                'school_year_id' => $request->school_year_id,
                'student_id' => $request->student_id,
                'status' => $request->status,
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
            return redirect()->route('guardian.enrollments.index')->with('error', 'Enrollment is currently closed. No active enrollment period available.');
        }

        if (! $activePeriod->isOpen()) {
            return redirect()->route('guardian.enrollments.index')->with('error', 'Enrollment period is not currently open. The deadline has passed.');
        }

        // Get Guardian model for authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();
        $currentSchoolYearId = $activePeriod->school_year_id;
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

        $students = $studentsQuery->map(function ($student) use ($currentSchoolYearId) {
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
                    $student->getAvailableGradeLevels($currentSchoolYearId)
                ),
            ];
        });

        return Inertia::render('guardian/enrollments/create', [
            'students' => $students,
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
            'currentSchoolYear' => $activePeriod->schoolYear->name,
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

        // Check for existing enrollment in the same school year
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('school_year_id', $activePeriod->school_year_id)
            ->exists();

        if ($existingEnrollment) {
            return back()->withErrors([
                'student_id' => 'This student already has an enrollment for the current school year.',
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

        // Get the fee for the selected grade level and enrollment period
        $gradeLevelFee = \App\Models\GradeLevelFee::where('grade_level', $validated['grade_level'])
            ->where('enrollment_period_id', $activePeriod->id)
            ->first();

        $tuitionFeeCents = $gradeLevelFee ? $gradeLevelFee->tuition_fee_cents : 0;
        $miscFeeCents = $gradeLevelFee ? $gradeLevelFee->miscellaneous_fee_cents : 0;
        $laboratoryFeeCents = $gradeLevelFee ? $gradeLevelFee->laboratory_fee_cents : 0;
        $libraryFeeCents = $gradeLevelFee ? $gradeLevelFee->library_fee_cents : 0;
        $sportsFeeCents = $gradeLevelFee ? $gradeLevelFee->sports_fee_cents : 0;
        $otherFeeCents = $gradeLevelFee ? $gradeLevelFee->other_fees_cents : 0;
        $discountCents = 0;

        // Calculate totals
        $totalAmountCents = $tuitionFeeCents + $miscFeeCents + $laboratoryFeeCents + $libraryFeeCents + $sportsFeeCents + $otherFeeCents;
        $netAmountCents = $totalAmountCents - $discountCents;
        $amountPaidCents = 0;
        $balanceCents = $netAmountCents - $amountPaidCents;

        // Get Guardian model ID for the authenticated user
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();

        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'guardian_id' => $guardian->id,
            'school_year_id' => $activePeriod->school_year_id,
            'enrollment_period_id' => $activePeriod->id,
            'quarter' => Quarter::from($validated['quarter']),
            'grade_level' => GradeLevel::from($validated['grade_level']),
            'status' => EnrollmentStatus::PENDING,
            'payment_plan' => $validated['payment_plan'],
            'tuition_fee_cents' => $tuitionFeeCents,
            'miscellaneous_fee_cents' => $miscFeeCents,
            'laboratory_fee_cents' => $laboratoryFeeCents,
            'library_fee_cents' => $libraryFeeCents,
            'sports_fee_cents' => $sportsFeeCents,
            'other_fees_cents' => $otherFeeCents,
            'total_amount_cents' => $totalAmountCents,
            'discount_cents' => $discountCents,
            'net_amount_cents' => $netAmountCents,
            'payment_status' => PaymentStatus::PENDING,
            'amount_paid_cents' => $amountPaidCents,
            'balance_cents' => $balanceCents,
        ]);

        // Load relationships for notifications
        $enrollment->load(['student', 'guardian.user', 'schoolYear']);

        // Dispatch event to notify registrars - EnrollmentObserver will handle guardian notification
        // NotifyRegistrarOfNewEnrollment listener will handle registrar notifications
        event(new \App\Events\EnrollmentCreated($enrollment));

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

        $enrollment->load(['student', 'guardian', 'schoolYear']);

        // Ensure enrollment payment details are up-to-date
        $enrollment->updatePaymentDetails();

        // Load payments for this enrollment through the payments relationship
        $paymentsCollection = $enrollment->payments()
            ->orderBy('payment_date', 'desc')
            ->get();

        $payments = [];
        $runningBalance = $enrollment->net_amount_cents; // Start with the total net amount due for the enrollment

        foreach ($paymentsCollection->sortBy('payment_date') as $payment) {
            $runningBalance -= ($payment->amount * 100); // Subtract payment amount (converted to cents)
            $payments[] = [
                'id' => $payment->id,
                'payment_date' => $payment->payment_date->toISOString(),
                'amount' => $payment->amount * 100, // Amount in cents
                'payment_method' => $payment->payment_method->value,
                'reference_number' => $payment->reference_number,
                'balance_after_cents' => $runningBalance, // Add balance after this payment
            ];
        }

        return Inertia::render('guardian/enrollments/show', [
            'enrollment' => [
                'id' => $enrollment->id,
                'student' => $enrollment->student,
                'school_year' => $enrollment->schoolYear->name,
                'grade_level' => $enrollment->grade_level,
                'section' => $enrollment->section,
                'adviser' => $enrollment->adviser,
                'quarter' => $enrollment->quarter,
                'status' => $enrollment->status,
                'payment_status' => $enrollment->payment_status,
                'tuition_fee_cents' => $enrollment->tuition_fee_cents,
                'miscellaneous_fee_cents' => $enrollment->miscellaneous_fee_cents,
                'laboratory_fee_cents' => $enrollment->laboratory_fee_cents,
                'library_fee_cents' => $enrollment->library_fee_cents,
                'other_fees_cents' => $enrollment->other_fees_cents,
                'total_amount_cents' => $enrollment->total_amount_cents,
                'discount_cents' => $enrollment->discount_cents,
                'net_amount_cents' => $enrollment->net_amount_cents,
                'amount_paid_cents' => $enrollment->amount_paid_cents,
                'balance_cents' => $enrollment->balance_cents,
                'created_at' => $enrollment->created_at->toISOString(),
            ],
            'payments' => $payments,
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
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
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

        $validated = $request->validated();

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

        // Load enrollment with related data
        $enrollment->load([
            'student',
            'invoices.payments',
            'schoolYear',
        ]);

        $schoolAddress = SchoolInformation::getByKey('school_address', 'Lantapan, Bukidnon');
        $schoolPhone = SchoolInformation::getByKey('school_phone', '');
        $schoolEmail = SchoolInformation::getByKey('school_email', 'cbhlc@example.com');

        // Get all payments for this enrollment through invoices
        $payments = collect();
        foreach ($enrollment->invoices as $invoice) {
            $payments = $payments->merge($invoice->payments);
        }
        $payments = $payments->sortBy('payment_date');

        $pdf = Pdf::loadView('pdf.payment-history', [
            'enrollment' => $enrollment,
            'payments' => $payments,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
            'schoolEmail' => $schoolEmail,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("payment-history-{$enrollment->enrollment_id}.pdf");
    }

    /**
     * Download enrollment certificate PDF
     */
    public function downloadCertificate(Enrollment $enrollment)
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

        // Only allow certificate download for enrolled status
        if ($enrollment->status !== EnrollmentStatus::ENROLLED) {
            abort(403, 'Certificate only available for enrolled students.');
        }

        $enrollment->load('student', 'guardian', 'schoolYear');

        $schoolAddress = SchoolInformation::getByKey('school_address', 'Lantapan, Bukidnon');
        $schoolPhone = SchoolInformation::getByKey('school_phone', '');

        $pdf = Pdf::loadView('pdf.enrollment-certificate', [
            'enrollment' => $enrollment,
            'schoolAddress' => $schoolAddress,
            'schoolPhone' => $schoolPhone,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("enrollment-certificate-{$enrollment->enrollment_id}.pdf");
    }

    /**
     * Respond to information request from registrar.
     */
    public function respondToInfoRequest(Request $request, Enrollment $enrollment)
    {
        // Verify guardian has access to this enrollment
        $guardian = \App\Models\Guardian::where('user_id', Auth::id())->firstOrFail();
        $hasAccess = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $enrollment->student_id)
            ->exists();

        if (! $hasAccess) {
            abort(404);
        }

        // Validate that info was requested
        if (! $enrollment->info_requested) {
            return back()->with('error', 'No information request found for this enrollment.');
        }

        // Validate that info hasn't been responded to yet
        if ($enrollment->info_response_date) {
            return back()->with('error', 'You have already responded to this information request.');
        }

        $validated = $request->validate([
            'response_message' => 'required|string|max:2000',
        ]);

        $enrollment->update([
            'info_response_message' => $validated['response_message'],
            'info_response_date' => now(),
        ]);

        return back()->with('success', 'Your response has been submitted successfully. The registrar will review your information.');
    }
}
