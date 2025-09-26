<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/enrollments/index', [
            'enrollments' => [
                ['id' => 1, 'student_name' => 'John Doe', 'grade' => 'Grade 1', 'status' => 'pending'],
                ['id' => 2, 'student_name' => 'Jane Smith', 'grade' => 'Grade 2', 'status' => 'approved'],
            ],
            'filters' => request()->all(),
        ]);
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
