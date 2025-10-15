<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\Quarter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\StoreEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $enrollmentsQuery = Enrollment::query();

        $statusCounts = [
            'all' => (clone $enrollmentsQuery)->count(),
            'pending' => (clone $enrollmentsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $enrollmentsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $enrollmentsQuery)->where('status', 'rejected')->count(),
            'enrolled' => (clone $enrollmentsQuery)->where('status', 'enrolled')->count(),
            'completed' => (clone $enrollmentsQuery)->where('status', 'completed')->count(),
        ];

        $enrollments = $enrollmentsQuery->with(['student'])
            ->when($request->input('search'), function ($query, $search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->when($request->input('grade'), function ($query, $grade) {
                $query->where('grade_level', $grade);
            })
            ->latest()
            ->paginate(2)
            ->withQueryString();

        return Inertia::render('admin/enrollments/index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['search', 'status', 'grade']),
            'statusCounts' => $statusCounts,
        ]);
    }

    public function create(Request $request)
    {
        $currentSchoolYear = date('Y').'-'.(date('Y') + 1);
        $selectedStudentId = $request->query('student_id');

        // Get all students for admin (unlike guardian who only sees their students)
        $studentsQuery = Student::query()->get();

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

        return Inertia::render('shared/enrollments/create', [
            'students' => $students,
            'gradeLevels' => GradeLevel::values(),
            'quarters' => Quarter::values(),
            'currentSchoolYear' => $currentSchoolYear,
            'selectedStudentId' => $selectedStudentId,
            'submitRoute' => route('admin.enrollments.store'),
            'indexRoute' => route('admin.enrollments.index'),
        ]);
    }

    public function store(StoreEnrollmentRequest $request)
    {
        $validated = $request->validated();

        $student = Student::findOrFail($validated['student_id']);

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

        // Get the guardian for this student (use the first guardian)
        $guardian = $student->guardians()->first();

        if (! $guardian) {
            return back()->withErrors(['student_id' => 'This student does not have an associated guardian.']);
        }

        /** @var Guardian $guardian */
        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'guardian_id' => $guardian->id,
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
            'amount_paid_cents' => $amountPaidCents,
            'balance_cents' => $balanceCents,
        ]);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment application submitted successfully.');
    }

    public function show($id)
    {
        return Inertia::render('admin/enrollments/show', [
            'enrollment' => [
                'id' => $id,
                'student_name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'pending',
                'submitted_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('admin/enrollments/edit', [
            'enrollment' => [
                'id' => $id,
                'student_name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'pending',
            ],
        ]);
    }
}
