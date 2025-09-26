<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreEnrollmentRequest;
use App\Http\Requests\SuperAdmin\UpdateEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Enrollment::class);

        $query = Enrollment::with(['student', 'guardian.user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            })->orWhere('reference_number', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by grade level
        if ($request->filled('grade')) {
            $query->where('grade_level', $request->get('grade'));
        }

        // Filter by school year
        if ($request->filled('school_year')) {
            $query->where('school_year', $request->get('school_year'));
        }

        $enrollments = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('super-admin/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['search', 'status', 'grade', 'school_year']),
            'statuses' => EnrollmentStatus::cases(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Enrollment::class);

        $students = Student::with('guardians')->get();
        $guardians = Guardian::with('user')->get();

        return Inertia::render('super-admin/enrollments/create', [
            'students' => $students,
            'guardians' => $guardians,
            'gradelevels' => \App\Enums\GradeLevel::cases(),
            'quarters' => \App\Enums\Quarter::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnrollmentRequest $request)
    {
        Gate::authorize('create', Enrollment::class);

        $validated = $request->validated();

        // Check if student can enroll
        $student = Student::findOrFail($validated['student_id']);
        if (! $this->enrollmentService->canEnroll($student, $validated['school_year'])) {
            return redirect()->back()
                ->withErrors(['student_id' => 'Student already has a pending enrollment for this school year.']);
        }

        DB::transaction(function () use ($validated) {
            $enrollment = $this->enrollmentService->createEnrollment($validated);

            // Auto-approve if created by super admin
            if (auth()->user()->hasRole('super_admin')) {
                $this->enrollmentService->approveEnrollment($enrollment);
            }
        });

        return redirect()->route('super-admin.enrollments.index')
            ->with('success', 'Enrollment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        Gate::authorize('view', $enrollment);

        $enrollment->load(['student', 'guardian.user', 'documents']);

        return Inertia::render('super-admin/enrollments/show', [
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment)
    {
        Gate::authorize('update', $enrollment);

        $enrollment->load(['student', 'guardian']);
        $students = Student::with('guardians')->get();
        $guardians = Guardian::with('user')->get();

        return Inertia::render('super-admin/enrollments/edit', [
            'enrollment' => $enrollment,
            'students' => $students,
            'guardians' => $guardians,
            'gradelevels' => \App\Enums\GradeLevel::cases(),
            'quarters' => \App\Enums\Quarter::cases(),
            'statuses' => EnrollmentStatus::cases(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        Gate::authorize('update', $enrollment);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $enrollment) {
            $oldStatus = $enrollment->status;

            $enrollment->update($validated);

            // Handle status changes
            if ($oldStatus !== $validated['status']) {
                if ($validated['status'] === EnrollmentStatus::APPROVED->value) {
                    $this->enrollmentService->approveEnrollment($enrollment);
                } elseif ($validated['status'] === EnrollmentStatus::REJECTED->value) {
                    $this->enrollmentService->rejectEnrollment($enrollment, 'Updated by admin');
                }
            }
        });

        return redirect()->route('super-admin.enrollments.index')
            ->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        Gate::authorize('delete', $enrollment);

        // Only allow deletion of pending enrollments
        if ($enrollment->status !== EnrollmentStatus::PENDING) {
            return redirect()->route('super-admin.enrollments.index')
                ->with('error', 'Can only delete pending enrollments.');
        }

        $enrollment->delete();

        return redirect()->route('super-admin.enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }

    /**
     * Approve an enrollment
     */
    public function approve(Enrollment $enrollment)
    {
        Gate::authorize('approve-enrollment');

        try {
            $this->enrollmentService->approveEnrollment($enrollment);

            return redirect()->route('super-admin.enrollments.index')
                ->with('success', 'Enrollment approved successfully.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.enrollments.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject an enrollment
     */
    public function reject(Request $request, Enrollment $enrollment)
    {
        Gate::authorize('reject-enrollment');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        try {
            $this->enrollmentService->rejectEnrollment($enrollment, $validated['reason']);

            return redirect()->route('super-admin.enrollments.index')
                ->with('success', 'Enrollment rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.enrollments.index')
                ->with('error', $e->getMessage());
        }
    }
}
