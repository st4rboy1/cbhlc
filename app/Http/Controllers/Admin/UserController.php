<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/users/index', [
            'users' => [
                ['id' => 1, 'name' => 'Registrar User', 'email' => 'registrar@cbhlc.edu', 'role' => 'registrar'],
                ['id' => 2, 'name' => 'Guardian User', 'email' => 'parent@example.com', 'role' => 'guardian'],
            ],
            'total' => 2,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('admin/users/show', [
            'user' => [
                'id' => $id,
                'name' => 'Registrar User',
                'email' => 'registrar@cbhlc.edu',
                'role' => 'registrar',
                'created_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('admin/users/edit', [
            'user' => [
                'id' => $id,
                'name' => 'Registrar User',
                'email' => 'registrar@cbhlc.edu',
                'role' => 'registrar',
            ],
            'roles' => ['administrator', 'registrar', 'guardian', 'student'],
        ]);
    }
}
