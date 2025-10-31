<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreStudentRequest;
use App\Http\Requests\SuperAdmin\UpdateStudentRequest;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $query = Student::with(['guardians', 'enrollments' => function ($q) {
            $q->latest()->limit(1);
        }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by grade level
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->get('grade_level'));
        }

        // Filter by enrollment status
        if ($request->filled('status')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('status', $request->get('status'))
                    ->latest()
                    ->limit(1);
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('super-admin/students/index', [
            'students' => $students,
            'filters' => $request->only(['search', 'grade_level', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Student::class);

        $guardians = Guardian::with('user')->get();

        return Inertia::render('super-admin/students/create', [
            'guardians' => $guardians,
            'gradelevels' => \App\Enums\GradeLevel::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        Gate::authorize('create', Student::class);

        $validated = $request->validated();

        $student = DB::transaction(function () use ($validated) {
            $student = Student::create([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'birth_place' => $validated['birth_place'],
                'gender' => $validated['gender'],
                'nationality' => $validated['nationality'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'grade_level' => $validated['grade_level'],
            ]);

            // Attach guardians
            foreach ($validated['guardian_ids'] as $index => $guardianId) {
                $student->guardians()->attach($guardianId, [
                    'relationship_type' => 'guardian',
                    'is_primary' => $index === 0,
                ]);
            }

            return $student;
        });

        // Dispatch event to notify registrars
        event(new \App\Events\StudentCreated($student));

        return redirect()->route('super-admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        Gate::authorize('view', $student);

        $student->load(['guardians.user', 'enrollments']);

        // Get the latest enrollment status
        $latestEnrollment = $student->enrollments()->latest()->first();
        $status = $latestEnrollment?->status?->label() ?? 'No Enrollment';

        // Transform student data to match frontend expectations
        $studentData = [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'grade' => $student->grade_level?->label() ?? 'N/A',
            'status' => $status,
            'birth_date' => $student->birthdate->format('F d, Y'),
            'address' => $student->address ?? 'N/A',
            'guardians' => $student->guardians,
        ];

        return Inertia::render('super-admin/students/show', [
            'student' => $studentData,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        Gate::authorize('update', $student);

        $student->load('guardians');
        $guardians = Guardian::with('user')->get();

        return Inertia::render('super-admin/students/edit', [
            'student' => $student,
            'guardians' => $guardians,
            'gradelevels' => \App\Enums\GradeLevel::cases(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        Gate::authorize('update', $student);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $student) {
            $student->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'birth_place' => $validated['birth_place'] ?? null,
                'gender' => $validated['gender'],
                'nationality' => $validated['nationality'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'grade_level' => $validated['grade'],
            ]);

            // Sync guardians
            $syncData = [];
            foreach ($validated['guardian_ids'] as $index => $guardianId) {
                $syncData[$guardianId] = [
                    'relationship_type' => 'guardian',
                    'is_primary' => $index === 0,
                ];
            }
            $student->guardians()->sync($syncData);
        });

        return redirect()->route('super-admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        Gate::authorize('delete', $student);

        // Check if student has enrollments
        if ($student->enrollments()->exists()) {
            return redirect()->route('super-admin.students.index')
                ->with('error', 'Cannot delete student with existing enrollments.');
        }

        $student->delete();

        return redirect()->route('super-admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Display the student's enrollments.
     */
    public function enrollments(Student $student)
    {
        Gate::authorize('view', $student);

        $enrollments = $student->enrollments()
            ->with(['guardian', 'schoolYear'])
            ->latest()
            ->get()
            ->map(function (Enrollment $enrollment): array {
                return [
                    'id' => $enrollment->id,
                    'enrollment_id' => $enrollment->enrollment_id,
                    'status' => $enrollment->status->value,
                    'grade_level' => $enrollment->grade_level->value,
                    'quarter' => $enrollment->quarter->value,
                    'school_year' => $enrollment->schoolYear ? $enrollment->schoolYear->name : 'N/A',
                    'guardian' => [
                        'id' => $enrollment->guardian->id,
                        'first_name' => $enrollment->guardian->first_name,
                        'last_name' => $enrollment->guardian->last_name,
                    ],
                    'created_at' => $enrollment->created_at?->toISOString() ?? '',
                ];
            });

        return Inertia::render('super-admin/students/enrollments', [
            'student' => $student,
            'enrollments' => $enrollments,
        ]);
    }
}
