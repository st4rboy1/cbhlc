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
        $student = Student::with(['guardians', 'enrollments'])->findOrFail($id);

        return Inertia::render('admin/students/show', [
            'student' => $student,
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
