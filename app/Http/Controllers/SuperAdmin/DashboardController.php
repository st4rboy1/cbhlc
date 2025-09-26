<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('super-admin/dashboard', [
            'stats' => [
                'total_students' => 150,
                'pending_enrollments' => 12,
                'active_users' => 45,
                'total_revenue' => 1250000,
            ],
            'message' => 'Super Admin Dashboard',
        ]);
    }
}
