<?php

namespace App\Http\Controllers\SuperAdmin;

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

    public function index()
    {
        $currentYearName = date('Y').'-'.(date('Y') + 1);
        $currentSchoolYear = SchoolYear::where('name', $currentYearName)->first();
        $currentSchoolYearId = $currentSchoolYear?->id;

        $quickStats = $this->dashboardService->getQuickStats();
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
        $totalUsers = User::count();
        $verificationRate = $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 1) : 0;

        // Recent verification activity (last 24 hours)
        $recentVerifications = \App\Models\EmailVerificationEvent::where('verified_at', '>=', now()->subDay())->count();

        // Average time to verify (in hours)
        $avgTimeToVerify = \App\Models\EmailVerificationEvent::avg('time_to_verify_minutes');
        $avgTimeToVerifyHours = $avgTimeToVerify ? round($avgTimeToVerify / 60, 1) : null;

        // Verification stats for this week
        $weeklyRegistrations = User::where('created_at', '>=', now()->startOfWeek())->count();
        $weeklyVerifications = \App\Models\EmailVerificationEvent::where('verified_at', '>=', now()->startOfWeek())->count();
        $weeklyVerificationRate = $weeklyRegistrations > 0 ? round(($weeklyVerifications / $weeklyRegistrations) * 100, 1) : 0;

        // Recent verification events with user details
        $recentVerificationEvents = \App\Models\EmailVerificationEvent::with('user')
            ->latest('verified_at')
            ->limit(5)
            ->get()
            ->map(function ($event) {
                return [
                    'user_name' => $event->user->name,
                    'user_email' => $event->user->email,
                    'verified_at' => $event->verified_at->format('M d, Y g:i A'),
                    'time_to_verify' => $event->time_to_verify_minutes ? round($event->time_to_verify_minutes / 60, 1).' hours' : 'N/A',
                ];
            });

        // Student enrollment journey
        $studentsWithEnrollments = Student::has('enrollments')->count();
        $studentsWithoutEnrollments = Student::count() - $studentsWithEnrollments;

        // Payment journey metrics
        $enrollmentsNeedingPayment = Enrollment::where('status', \App\Enums\EnrollmentStatus::ENROLLED)
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
            'total_students' => $quickStats['total_students'],
            'active_enrollments' => $quickStats['active_enrollments'],
            'pending_enrollments' => $quickStats['pending_enrollments'],
            'total_revenue' => $quickStats['total_revenue'],

            // User Journey Metrics
            'total_users' => User::count(),
            'verified_users' => $verifiedUsers,
            'unverified_users' => $unverifiedUsers,
            'verification_rate' => $verificationRate,
            'recent_verifications_24h' => $recentVerifications,
            'avg_time_to_verify_hours' => $avgTimeToVerifyHours,
            'weekly_registrations' => $weeklyRegistrations,
            'weekly_verifications' => $weeklyVerifications,
            'weekly_verification_rate' => $weeklyVerificationRate,
            'recent_verification_events' => $recentVerificationEvents,

            // Guardian Journey Metrics
            'total_guardians' => $totalGuardians,
            'guardians_with_students' => $guardiansWithStudents,
            'guardians_without_students' => $guardiansWithoutStudents,
            'guardians_with_students_no_enrollments' => $guardiansWithStudentsNoEnrollments,

            // Student Journey Metrics
            'students_with_enrollments' => $studentsWithEnrollments,
            'students_without_enrollments' => $studentsWithoutEnrollments,

            // Enrollment metrics
            'approved_enrollments' => $enrollmentStats['approved'],
            'completed_enrollments' => $enrollmentStats['completed'],
            'rejected_enrollments' => $enrollmentStats['rejected'],
            'enrollments_needing_payment' => $enrollmentsNeedingPayment,

            // Payment metrics
            'total_invoices' => Invoice::count(),
            'paid_invoices' => $paymentStats['by_status']['paid'],
            'partial_payments' => $paymentStats['by_status']['partial'],
            'pending_payments' => $paymentStats['by_status']['pending'],
            'total_collected' => $paymentStats['total_collected'],
            'total_balance' => $paymentStats['total_balance'],
            'collection_rate' => $paymentStats['collection_rate'],

            // Financial Projections
            'total_expected_revenue' => $totalExpected,
            'potential_incoming_revenue' => $potentialRevenue,

            // Transaction metrics
            'total_payments' => Payment::count(),
            'recent_payments_count' => Payment::where('created_at', '>=', now()->subDays(7))->count(),

            // Document Verification Metrics
            'total_documents' => $totalDocuments,
            'pending_documents' => $pendingDocuments,
            'verified_documents' => $verifiedDocuments,
            'rejected_documents' => $rejectedDocuments,
            'students_all_docs_verified' => $studentsWithAllDocsVerified,
            'students_pending_docs' => $studentsWithPendingDocs,
            'students_rejected_docs' => $studentsWithRejectedDocs,
        ];

        return Inertia::render('super-admin/super-admin-dashboard', [
            'stats' => $stats,
            'activeSchoolYear' => $activeSchoolYear,
            'schoolYears' => $allSchoolYears,
        ]);
    }
}
