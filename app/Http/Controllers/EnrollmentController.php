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

        // If user is a guardian, get their students
        if ($user->hasRole('guardian')) {
            $studentIds = GuardianStudent::where('guardian_id', $user->id)
                ->pluck('student_id');
            $students = Student::whereIn('id', $studentIds)->get();
        }

        return Inertia::render('enrollments/create', [
            'students' => $students,
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
            'currentSchoolYear' => date('Y').'-'.(date('Y') + 1),
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
        ]);

        Enrollment::create([
            'student_id' => $validated['student_id'],
            'guardian_id' => Auth::id(),
            'school_year' => $validated['school_year'],
            'quarter' => $validated['quarter'],
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
