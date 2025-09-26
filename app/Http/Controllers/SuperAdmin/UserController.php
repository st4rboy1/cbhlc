<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('super-admin/users/index', [
            'users' => [
                ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@cbhlc.edu', 'role' => 'administrator'],
                ['id' => 2, 'name' => 'Registrar User', 'email' => 'registrar@cbhlc.edu', 'role' => 'registrar'],
                ['id' => 3, 'name' => 'Guardian User', 'email' => 'parent@example.com', 'role' => 'guardian'],
            ],
            'total' => 3,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('super-admin/users/show', [
            'user' => [
                'id' => $id,
                'name' => 'Admin User',
                'email' => 'admin@cbhlc.edu',
                'role' => 'administrator',
                'created_at' => now()->toDateTimeString(),
                'permissions' => [],
            ],
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('super-admin/users/edit', [
            'user' => [
                'id' => $id,
                'name' => 'Admin User',
                'email' => 'admin@cbhlc.edu',
                'role' => 'administrator',
            ],
            'roles' => ['super_admin', 'administrator', 'registrar', 'guardian', 'student'],
        ]);
    }
}
