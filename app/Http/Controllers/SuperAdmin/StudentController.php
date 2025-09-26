<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        return Inertia::render('super-admin/students/index', [
            'students' => [
                ['id' => 1, 'name' => 'John Doe', 'grade' => 'Grade 1', 'status' => 'active'],
                ['id' => 2, 'name' => 'Jane Smith', 'grade' => 'Grade 2', 'status' => 'active'],
                ['id' => 3, 'name' => 'Bob Johnson', 'grade' => 'Grade 3', 'status' => 'inactive'],
            ],
            'total' => 3,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('super-admin/students/show', [
            'student' => [
                'id' => $id,
                'name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'active',
                'birth_date' => '2015-01-01',
                'address' => '123 Main St',
                'guardians' => [],
            ],
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('super-admin/students/edit', [
            'student' => [
                'id' => $id,
                'name' => 'John Doe',
                'grade' => 'Grade 1',
                'status' => 'active',
            ],
        ]);
    }
}
