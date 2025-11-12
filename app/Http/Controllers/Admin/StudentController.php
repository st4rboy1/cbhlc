<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all()->map(function ($student) {
            /** @var \App\Models\Student $student */
            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'grade' => $student->grade_level?->label() ?? 'N/A',
                'status' => 'active', // Placeholder
            ];
        });

        return Inertia::render('admin/students/index', [
            'students' => $students,
            'total' => $students->count(),
        ]);
    }

    public function show($id)
    {
        $student = Student::with(['guardians.user', 'enrollments'])->findOrFail($id);

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

        return Inertia::render('admin/students/show', [
            'student' => $studentData,
        ]);
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);

        return Inertia::render('admin/students/edit', [
            'student' => $student,
        ]);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }
}
