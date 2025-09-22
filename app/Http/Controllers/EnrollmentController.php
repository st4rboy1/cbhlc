<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\Quarter;
use App\Models\Enrollment;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments.
     */
    public function index()
    {
        $user = Auth::user();

        // Build query based on user role
        $query = Enrollment::with(['student', 'guardian']);

        // If user is a guardian, only show their children's enrollments
        if ($user->hasRole('guardian')) {
            // Get student IDs for this guardian
            $studentIds = GuardianStudent::where('guardian_id', $user->id)
                ->pluck('student_id');

            $query->whereIn('student_id', $studentIds);
        }

        $enrollments = $query->latest('created_at')->paginate(10);

        return Inertia::render('enrollments/index', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create()
    {
        $user = Auth::user();
        $students = [];
        $currentSchoolYear = date('Y').'-'.(date('Y') + 1);

        // If user is a guardian, get their students with enrollment info
        if ($user->hasRole('guardian')) {
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
        }

        return Inertia::render('enrollments/create', [
            'students' => $students,
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
            'currentSchoolYear' => $currentSchoolYear,
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

        // Check if student already has an enrollment for this school year
        $existingEnrollment = Enrollment::where('student_id', $validated['student_id'])
            ->where('school_year', $validated['school_year'])
            ->first();

        if ($existingEnrollment) {
            return redirect()->back()
                ->withErrors(['student_id' => 'This student already has an enrollment for the '.$validated['school_year'].' school year.'])
                ->withInput();
        }

        // Check if student already has a pending enrollment
        $pendingEnrollment = Enrollment::where('student_id', $validated['student_id'])
            ->where('status', EnrollmentStatus::PENDING)
            ->first();

        if ($pendingEnrollment) {
            return redirect()->back()
                ->withErrors(['student_id' => 'This student already has a pending enrollment. Please wait for it to be processed before creating a new one.'])
                ->withInput();
        }

        // Check if student has an active enrollment (enrolled status)
        $activeEnrollment = Enrollment::where('student_id', $validated['student_id'])
            ->where('status', EnrollmentStatus::ENROLLED)
            ->first();

        if ($activeEnrollment) {
            return redirect()->back()
                ->withErrors(['student_id' => 'This student is currently enrolled in '.$activeEnrollment->school_year.'. Please wait for the school year to be completed before enrolling for the next year.'])
                ->withInput();
        }

        // Validate grade level progression
        $requestedGrade = \App\Enums\GradeLevel::from($validated['grade_level']);
        $availableGrades = $student->getAvailableGradeLevels($validated['school_year']);

        if (! in_array($requestedGrade, $availableGrades)) {
            $currentGrade = $student->getCurrentGradeLevel();
            $errorMessage = $currentGrade
                ? "Based on current grade ({$currentGrade->value}), this student cannot enroll in {$requestedGrade->value}."
                : 'This grade level is not available for this student.';

            return redirect()->back()
                ->withErrors(['grade_level' => $errorMessage])
                ->withInput();
        }

        // Set quarter logic: new students can choose, existing students default to First
        $quarter = $student->isNewStudent()
            ? $validated['quarter']
            : Quarter::FIRST->value;

        Enrollment::create([
            'student_id' => $validated['student_id'],
            'guardian_id' => Auth::id(),
            'school_year' => $validated['school_year'],
            'quarter' => $quarter,
            'grade_level' => $validated['grade_level'],
            'status' => EnrollmentStatus::PENDING,
            'tuition_fee_cents' => 0,
            'miscellaneous_fee_cents' => 0,
            'laboratory_fee_cents' => 0,
            'total_amount_cents' => 0,
            'net_amount_cents' => 0,
            'amount_paid_cents' => 0,
            'balance_cents' => 0,
            'payment_status' => \App\Enums\PaymentStatus::PENDING,
        ]);

        return redirect()->route('enrollments.index')
            ->with('success', 'Enrollment application submitted successfully.');
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        // Check if user can view this enrollment
        $user = Auth::user();

        if ($user->hasRole('guardian')) {
            // Guardian can only view their children's enrollments
            $studentIds = GuardianStudent::where('guardian_id', $user->id)
                ->pluck('student_id');

            if (! $studentIds->contains($enrollment->student_id)) {
                abort(403, 'Unauthorized');
            }
        }

        $enrollment->load(['student', 'guardian']);

        return Inertia::render('enrollments/show', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(Enrollment $enrollment)
    {
        // Check if user can edit this enrollment
        $user = Auth::user();

        if ($user->hasRole('guardian')) {
            // Guardian can only edit pending enrollments for their children
            if ($enrollment->guardian_id !== $user->id ||
                $enrollment->status !== EnrollmentStatus::PENDING) {
                abort(403, 'Unauthorized');
            }
        }

        return Inertia::render('enrollments/edit', [
            'enrollment' => $enrollment->load('student'),
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
        ]);
    }

    /**
     * Update the specified enrollment in storage.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        // Check if user can update this enrollment
        $user = Auth::user();

        if ($user->hasRole('guardian')) {
            // Guardian can only update pending enrollments for their children
            if ($enrollment->guardian_id !== $user->id ||
                $enrollment->status !== EnrollmentStatus::PENDING) {
                abort(403, 'Unauthorized');
            }
        }

        $validated = $request->validate([
            'grade_level' => 'sometimes|string',
            'quarter' => 'sometimes|string',
        ]);

        $enrollment->update($validated);

        return redirect()->route('enrollments.show', $enrollment)
            ->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Remove the specified enrollment from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        // Check if user can delete this enrollment
        $user = Auth::user();

        if ($user->hasRole('guardian')) {
            // Guardian can only delete pending enrollments for their children
            if ($enrollment->guardian_id !== $user->id ||
                $enrollment->status !== EnrollmentStatus::PENDING) {
                abort(403, 'Unauthorized');
            }
        }

        $enrollment->delete();

        return redirect()->route('enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }
}
