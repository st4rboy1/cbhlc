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
        $currentYear = date('Y').'-'.(date('Y') + 1);
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

        // Student enrollment journey
        $studentsWithEnrollments = Student::has('enrollments')->count();
        $studentsWithoutEnrollments = Student::count() - $studentsWithEnrollments;

        // Payment journey metrics
        $enrollmentsNeedingPayment = Enrollment::where('status', \App\Enums\EnrollmentStatus::ENROLLED)
            ->where('payment_status', '!=', \App\Enums\PaymentStatus::PAID)
            ->count();

        // Financial projections
        $totalExpected = Enrollment::where('school_year', $currentYear)
            ->sum('net_amount_cents') / 100;
        $potentialRevenue = Enrollment::where('school_year', $currentYear)
            ->sum('balance_cents') / 100;

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
