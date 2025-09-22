<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of all students.
     */
    public function index(Request $request)
    {
        $query = Student::with(['enrollments' => function ($q) {
            $q->latest('created_at')->limit(1);
        }]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply grade level filter
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Apply section filter
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        $students = $query->paginate(20);

        return Inertia::render('students/index', [
            'students' => $students,
            'filters' => $request->only(['search', 'grade_level', 'section']),
            'gradeLevels' => GradeLevel::values(),
        ]);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load(['enrollments.guardian', 'guardianStudents.guardian']);

        return Inertia::render('students/show', [
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
                'created_at' => $student->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $student->updated_at->format('Y-m-d H:i:s'),
                'enrollments' => $student->enrollments->map(function (\App\Models\Enrollment $enrollment) {
                    return [
                        'id' => $enrollment->id,
                        'school_year' => $enrollment->school_year,
                        'grade_level' => $enrollment->grade_level,
                        'quarter' => $enrollment->quarter,
                        'status' => $enrollment->status->value,
                        'payment_status' => $enrollment->payment_status->value,
                        'guardian_name' => $enrollment->guardian ?
                            $enrollment->guardian->first_name.' '.$enrollment->guardian->last_name : 'N/A',
                        'created_at' => $enrollment->created_at->format('Y-m-d'),
                        'approved_at' => $enrollment->approved_at?->format('Y-m-d'),
                    ];
                }),
                'guardians' => $student->guardianStudents->map(function ($gs) {
                    return [
                        'id' => $gs->guardian->id,
                        'name' => $gs->guardian->first_name.' '.$gs->guardian->last_name,
                        'email' => $gs->guardian->email,
                        'is_primary' => $gs->is_primary,
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
        return Inertia::render('students/create', [
            'gradeLevels' => GradeLevel::values(),
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'address' => 'required|string',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:students,email',
            'grade_level' => 'nullable|string|in:'.implode(',', GradeLevel::values()),
            'section' => 'nullable|string|max:50',
        ]);

        // Generate student ID
        $validated['student_id'] = 'CBHLC'.date('Y').str_pad((string) (Student::count() + 1), 4, '0', STR_PAD_LEFT);

        $student = Student::create($validated);

        return redirect()->route('registrar.students.show', $student->id)
            ->with('success', 'Student created successfully.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        return Inertia::render('students/edit', [
            'student' => $student,
            'gradeLevels' => GradeLevel::values(),
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'address' => 'required|string',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:students,email,'.$student->id,
            'grade_level' => 'nullable|string|in:'.implode(',', GradeLevel::values()),
            'section' => 'nullable|string|max:50',
        ]);

        $student->update($validated);

        return redirect()->route('registrar.students.show', $student->id)
            ->with('success', 'Student information updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        // Check if student has any enrollments
        if ($student->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete student with enrollment records.');
        }

        $student->delete();

        return redirect()->route('registrar.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Export students to Excel.
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Export functionality coming soon.');
    }
}
