<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PaymentStatus;
use App\Enums\Quarter;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of guardian's children enrollments.
     */
    public function index()
    {
        $user = Auth::user();

        // Get student IDs for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $user->id)
            ->pluck('student_id');

        $enrollments = Enrollment::with(['student', 'guardian'])
            ->whereIn('student_id', $studentIds)
            ->latest('created_at')
            ->paginate(10);

        return Inertia::render('guardian/enrollments/index', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $currentSchoolYear = date('Y').'-'.(date('Y') + 1);
        $selectedStudentId = $request->query('student_id');

        // Get guardian's students with enrollment info
        $studentIds = GuardianStudent::where('guardian_id', $user->id)
            ->pluck('student_id');
        $studentsQuery = Student::whereIn('id', $studentIds)->get();

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
        ]);
    }

    /**
     * Store a newly created enrollment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_year' => 'required|string',
            'quarter' => 'required|string',
            'grade_level' => 'required|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        // Verify this guardian has access to this student
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
            ->where('student_id', $validated['student_id'])
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to enroll this student.');
        }

        // Check for pending enrollments constraint
        $hasPendingEnrollment = Enrollment::where('student_id', $validated['student_id'])
            ->where('status', EnrollmentStatus::PENDING)
            ->exists();

        if ($hasPendingEnrollment) {
            return back()->withErrors(['student_id' => 'This student already has a pending enrollment. Please wait for it to be processed before submitting another one.']);
        }

        // Check for active enrollment (enrolled status)
        $hasActiveEnrollment = Enrollment::where('student_id', $validated['student_id'])
            ->where('status', EnrollmentStatus::ENROLLED)
            ->exists();

        if ($hasActiveEnrollment) {
            return back()->withErrors(['student_id' => 'This student has an active enrollment. Please wait for the current enrollment to be completed before applying for another year.']);
        }

        // Check if student already has an enrollment for this school year
        $existingEnrollment = Enrollment::where('student_id', $validated['student_id'])
            ->where('school_year', $validated['school_year'])
            ->exists();

        if ($existingEnrollment) {
            return back()->withErrors(['student_id' => 'This student already has an enrollment for the selected school year.']);
        }

        // Get the fee for the selected grade level and school year
        $gradeLevelFee = \App\Models\GradeLevelFee::where('grade_level', $validated['grade_level'])
            ->where('school_year', $validated['school_year'])
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

        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'guardian_id' => Auth::id(),
            'school_year' => $validated['school_year'],
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
        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
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
        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
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
        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
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
        // Verify this guardian has access to this enrollment
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
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
}
