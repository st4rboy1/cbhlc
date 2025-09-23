<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\StoreStudentRequest;
use App\Http\Requests\Guardian\UpdateStudentRequest;
use App\Models\GuardianStudent;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of guardian's students.
     */
    public function index()
    {
        $user = Auth::user();

        // Get all students for this guardian
        $studentIds = GuardianStudent::where('guardian_id', $user->id)
            ->pluck('student_id');

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $students */
        $students = Student::with(['enrollments' => function ($query) {
            $query->latest('created_at')->limit(1);
        }])
            ->whereIn('id', $studentIds)
            ->get()
            /** @phpstan-ignore-next-line */
            ->map(function (Student $student) {
                $latestEnrollment = $student->enrollments->first();

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->first_name.' '.
                                  ($student->middle_name ? $student->middle_name.' ' : '').
                                  $student->last_name,
                    'birthdate' => $student->birthdate,
                    'gender' => $student->gender,
                    'grade_level' => $student->grade_level,
                    'latest_enrollment' => $latestEnrollment ? [
                        'school_year' => $latestEnrollment->school_year,
                        'status' => $latestEnrollment->status->value,
                        'grade_level' => $latestEnrollment->grade_level,
                    ] : null,
                ];
            });

        return Inertia::render('guardian/students/index', [
            'students' => $students,
        ]);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        // Verify this guardian has access to this student
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
            ->where('student_id', $student->id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to view this student.');
        }

        $student->load('enrollments');

        return Inertia::render('guardian/students/show', [
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'birthdate' => $student->birthdate,
                'gender' => $student->gender,
                'address' => $student->address,
                'contact_number' => $student->contact_number,
                'email' => $student->email,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                /** @phpstan-ignore-next-line */
                'enrollments' => $student->enrollments->map(function (\App\Models\Enrollment $enrollment) {
                    return [
                        'id' => $enrollment->id,
                        'school_year' => $enrollment->school_year,
                        'grade_level' => $enrollment->grade_level,
                        'quarter' => $enrollment->quarter,
                        'status' => $enrollment->status->value,
                        'payment_status' => $enrollment->payment_status->value,
                        'created_at' => $enrollment->created_at->format('Y-m-d'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return Inertia::render('guardian/students/create');
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        // Generate student ID
        $validated['student_id'] = 'CBHLC'.date('Y').str_pad((string) (Student::count() + 1), 4, '0', STR_PAD_LEFT);

        $student = Student::create($validated);

        // Link student to guardian
        GuardianStudent::create([
            'guardian_id' => Auth::id(),
            'student_id' => $student->id,
            'relationship_type' => 'mother', // Default relationship type
            'is_primary_contact' => true,
        ]);

        return redirect()->route('guardian.students.show', $student->id)
            ->with('success', 'Student added successfully.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        // Verify this guardian has access to this student
        $hasAccess = GuardianStudent::where('guardian_id', Auth::id())
            ->where('student_id', $student->id)
            ->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to edit this student.');
        }

        return Inertia::render('guardian/students/edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        $student->update($validated);

        return redirect()->route('guardian.students.show', $student->id)
            ->with('success', 'Student information updated successfully.');
    }
}
