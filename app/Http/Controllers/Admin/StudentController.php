<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        $studentData = [];
        foreach ($students as $student) {
            $studentData[] = [
                'id' => $student->id,
                'name' => $student->name,
                'grade' => $student->grade_level->name,
                'status' => 'active', // Placeholder
            ];
        }

        return Inertia::render('admin/students/index', [
            'students' => $studentData,
            'total' => $students->count(),
        ]);
    }

    public function show(Student $student)
    {
        return Inertia::render('admin/students/show', [
            'student' => $student,
        ]);
    }

    public function edit(Student $student)
    {
        return Inertia::render('admin/students/edit', [
            'student' => $student,
        ]);
    }
}
