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
        $student = Student::findOrFail($id);

        return Inertia::render('admin/students/show', [
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'grade' => $student->grade_level?->label() ?? 'N/A',
                'status' => 'active', // Assuming a default status or fetching from model if available
                'birth_date' => $student->birth_date?->format('Y-m-d'),
                'address' => $student->address,
                'email' => $student->email,
                'gender' => $student->gender,
                'contact_number' => $student->contact_number,
                'student_id' => $student->student_id,
            ],
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
