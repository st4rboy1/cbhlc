<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/students/index', [
            'students' => [
                ['id' => 1, 'name' => 'John Doe', 'grade' => 'Grade 1', 'status' => 'active'],
                ['id' => 2, 'name' => 'Jane Smith', 'grade' => 'Grade 2', 'status' => 'active'],
            ],
            'total' => 2,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('admin/students/show', [
            'student' => [
                'id' => $id,
                'name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'active',
                'birth_date' => '2015-01-01',
                'address' => '123 Main St',
            ],
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('admin/students/edit', [
            'student' => [
                'id' => $id,
                'name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'active',
            ],
        ]);
    }
}
