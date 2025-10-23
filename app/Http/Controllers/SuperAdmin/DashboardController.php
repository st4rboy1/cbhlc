<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function index()
    {
        $quickStats = $this->dashboardService->getQuickStats();
        $paymentStats = $this->dashboardService->getPaymentStatistics();
        $enrollmentStats = $this->dashboardService->getEnrollmentStatistics();

        $stats = [
            // Core metrics
            'total_students' => $quickStats['total_students'],
            'active_enrollments' => $quickStats['active_enrollments'],
            'pending_enrollments' => $quickStats['pending_enrollments'],
            'total_revenue' => $quickStats['total_revenue'],

            // User metrics
            'total_users' => User::count(),
            'total_guardians' => Guardian::count(),

            // Enrollment metrics
            'approved_enrollments' => $enrollmentStats['approved'],
            'completed_enrollments' => $enrollmentStats['completed'],
            'rejected_enrollments' => $enrollmentStats['rejected'],

            // Payment metrics
            'total_invoices' => Invoice::count(),
            'paid_invoices' => $paymentStats['by_status']['paid'],
            'partial_payments' => $paymentStats['by_status']['partial'],
            'pending_payments' => $paymentStats['by_status']['pending'],
            'total_collected' => $paymentStats['total_collected'],
            'total_balance' => $paymentStats['total_balance'],
            'collection_rate' => $paymentStats['collection_rate'],

            // Transaction metrics
            'total_payments' => Payment::count(),
            'recent_payments_count' => Payment::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return Inertia::render('super-admin/super-admin-dashboard', [
            'stats' => $stats,
        ]);
    }
}
