<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $currentYearName = date('Y').'-'.(date('Y') + 1);
        $currentSchoolYear = SchoolYear::where('name', $currentYearName)->first();
        $currentSchoolYearId = $currentSchoolYear?->id;

        $paymentStats = $this->dashboardService->getPaymentStatistics();
        $enrollmentStats = $this->dashboardService->getEnrollmentStatistics();

        // Guardian Journey Metrics
        $totalGuardians = Guardian::count();
        $guardiansWithStudents = Guardian::has('children')->count();
        $guardiansWithoutStudents = $totalGuardians - $guardiansWithStudents;

        // Get guardians with students but no enrollments
        $guardiansWithStudentsNoEnrollments = Guardian::has('children')
            ->whereDoesntHave('children.enrollments')
            ->count();

        // User verification metrics
        $unverifiedUsers = User::whereNull('email_verified_at')->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();

        // Student enrollment journey
        $studentsWithEnrollments = Student::has('enrollments')->count();
        $studentsWithoutEnrollments = Student::count() - $studentsWithEnrollments;

        // Payment journey metrics
        $enrollmentsNeedingPayment = Enrollment::where('status', EnrollmentStatus::ENROLLED)
            ->where('payment_status', '!=', \App\Enums\PaymentStatus::PAID)
            ->count();

        // Financial projections
        $totalExpected = $currentSchoolYearId
            ? Enrollment::where('school_year_id', $currentSchoolYearId)
                ->sum('net_amount_cents') / 100
            : 0;
        $potentialRevenue = $currentSchoolYearId
            ? Enrollment::where('school_year_id', $currentSchoolYearId)
                ->sum('balance_cents') / 100
            : 0;

        // Document Verification Metrics
        $totalDocuments = Document::count();
        $pendingDocuments = Document::where('verification_status', VerificationStatus::PENDING)->count();
        $verifiedDocuments = Document::where('verification_status', VerificationStatus::VERIFIED)->count();
        $rejectedDocuments = Document::where('verification_status', VerificationStatus::REJECTED)->count();

        // Students with document statuses
        $studentsWithAllDocsVerified = Student::whereHas('documents')
            ->whereDoesntHave('documents', function ($query) {
                $query->where('verification_status', '!=', VerificationStatus::VERIFIED);
            })
            ->count();

        $studentsWithPendingDocs = Student::whereHas('documents', function ($query) {
            $query->where('verification_status', VerificationStatus::PENDING);
        })->count();

        $studentsWithRejectedDocs = Student::whereHas('documents', function ($query) {
            $query->where('verification_status', VerificationStatus::REJECTED);
        })->count();

        // School Years
        $activeSchoolYear = SchoolYear::active();
        $allSchoolYears = SchoolYear::orderBy('start_year', 'desc')->get();

        $stats = [
            // Core metrics
            'totalStudents' => Student::count(),
            'activeEnrollments' => $currentSchoolYearId
                ? Enrollment::where('school_year_id', $currentSchoolYearId)
                    ->where('status', EnrollmentStatus::ENROLLED)
                    ->count()
                : 0,
            'newEnrollments' => Enrollment::where('created_at', '>=', now()->startOfMonth())->count(),
            'pendingApplications' => Enrollment::where('status', EnrollmentStatus::PENDING)->count(),
            'totalStaff' => User::role(['super_admin', 'administrator', 'registrar'])->count(),

            // User Journey Metrics
            'totalUsers' => User::count(),
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,

            // Guardian Journey Metrics
            'totalGuardians' => $totalGuardians,
            'guardiansWithStudents' => $guardiansWithStudents,
            'guardiansWithoutStudents' => $guardiansWithoutStudents,
            'guardiansWithStudentsNoEnrollments' => $guardiansWithStudentsNoEnrollments,

            // Student Journey Metrics
            'studentsWithEnrollments' => $studentsWithEnrollments,
            'studentsWithoutEnrollments' => $studentsWithoutEnrollments,

            // Enrollment metrics
            'approvedEnrollments' => $enrollmentStats['approved'],
            'completedEnrollments' => $enrollmentStats['completed'],
            'rejectedEnrollments' => $enrollmentStats['rejected'],
            'enrollmentsNeedingPayment' => $enrollmentsNeedingPayment,

            // Payment metrics
            'totalInvoices' => Invoice::count(),
            'paidInvoices' => $paymentStats['by_status']['paid'],
            'partialPayments' => $paymentStats['by_status']['partial'],
            'pendingPayments' => $paymentStats['by_status']['pending'],
            'totalCollected' => $paymentStats['total_collected'],
            'totalBalance' => $paymentStats['total_balance'],
            'collectionRate' => $paymentStats['collection_rate'],

            // Financial Projections
            'totalExpectedRevenue' => $totalExpected,
            'potentialIncomingRevenue' => $potentialRevenue,

            // Transaction metrics
            'totalPayments' => Payment::count(),
            'recentPaymentsCount' => Payment::where('created_at', '>=', now()->subDays(7))->count(),
            'totalRevenue' => $currentSchoolYearId
                ? Enrollment::where('school_year_id', $currentSchoolYearId)
                    ->sum('amount_paid_cents') / 100
                : 0,

            // Document Verification Metrics
            'totalDocuments' => $totalDocuments,
            'pendingDocuments' => $pendingDocuments,
            'verifiedDocuments' => $verifiedDocuments,
            'rejectedDocuments' => $rejectedDocuments,
            'studentsAllDocsVerified' => $studentsWithAllDocsVerified,
            'studentsPendingDocs' => $studentsWithPendingDocs,
            'studentsRejectedDocs' => $studentsWithRejectedDocs,
        ];

        $recentActivities = Enrollment::with('student')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Enrollment $enrollment) {
                if ($enrollment->student) {
                    return [
                        'id' => $enrollment->id,
                        'message' => 'New enrollment application from '.$enrollment->student->full_name,
                        'time' => $enrollment->created_at->diffForHumans(),
                    ];
                }

                return null;
            })
            ->filter();

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'activeSchoolYear' => $activeSchoolYear,
            'schoolYears' => $allSchoolYears,
        ]);
    }
}
