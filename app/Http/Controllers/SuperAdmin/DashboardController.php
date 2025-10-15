<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function index()
    {
        $quickStats = $this->dashboardService->getQuickStats();

        $stats = [
            'total_students' => $quickStats['total_students'],
            'pending_enrollments' => $quickStats['pending_enrollments'],
            'active_users' => $quickStats['active_enrollments'],
            'total_revenue' => $quickStats['total_revenue'],
        ];

        return Inertia::render('super-admin/super-admin-dashboard', [
            'stats' => $stats,
        ]);
    }
}
