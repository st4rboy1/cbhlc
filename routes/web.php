<?php

use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\EnrollmentPeriodController as AdminEnrollmentPeriodController;
use App\Http\Controllers\Admin\GradeLevelFeeController as AdminGradeLevelFeeController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SchoolInformationController as AdminSchoolInformationController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Guardian\BillingController as GuardianBillingController;
use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\Guardian\EnrollmentController as GuardianEnrollmentController;
use App\Http\Controllers\Guardian\StudentController as GuardianStudentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ApplicationController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Registrar\DashboardController as RegistrarDashboardController;
use App\Http\Controllers\Registrar\DocumentController as RegistrarDocumentController;
use App\Http\Controllers\Registrar\EnrollmentController as RegistrarEnrollmentController;
use App\Http\Controllers\Registrar\GradeLevelFeeController as RegistrarGradeLevelFeeController;
use App\Http\Controllers\Registrar\StudentController as RegistrarStudentController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\SuperAdmin\AuditLogController as SuperAdminAuditLogController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\DocumentController as SuperAdminDocumentController;
use App\Http\Controllers\SuperAdmin\EnrollmentController as SuperAdminEnrollmentController;
use App\Http\Controllers\SuperAdmin\EnrollmentPeriodController as SuperAdminEnrollmentPeriodController;
use App\Http\Controllers\SuperAdmin\GradeLevelFeeController as SuperAdminGradeLevelFeeController;
use App\Http\Controllers\SuperAdmin\GuardianController as SuperAdminGuardianController;
use App\Http\Controllers\SuperAdmin\InvoiceController as SuperAdminInvoiceController;
use App\Http\Controllers\SuperAdmin\PaymentController as SuperAdminPaymentController;
use App\Http\Controllers\SuperAdmin\ReceiptController as SuperAdminReceiptController;
use App\Http\Controllers\SuperAdmin\SchoolInformationController as SuperAdminSchoolInformationController;
use App\Http\Controllers\SuperAdmin\SchoolYearController as SuperAdminSchoolYearController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;
use App\Http\Controllers\SuperAdmin\StudentController as SuperAdminStudentController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\TuitionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/application', [ApplicationController::class, 'index'])->name('application');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Profile Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::redirect('/settings', '/settings/profile')->name('settings');
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */
    // Notifications page
    Route::get('/notifications', [NotificationController::class, 'page'])->name('notifications');

    // Notification API endpoints
    Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    });

    // Notification routes (for Inertia)
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    /*
    |--------------------------------------------------------------------------
    | Shared Routes (Temporarily - will be refactored per role)
    |--------------------------------------------------------------------------
    */

    // Payment Routes
    Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\PaymentController::class, 'downloadReceipt'])->name('payments.receipt');

    // Tuition Routes
    Route::get('/tuition', [TuitionController::class, 'index'])->name('tuition');

    // Academic Reports
    Route::get('/students/{student}/report', [StudentReportController::class, 'show'])
        ->name('students.report');

    // Shared Pages
    Route::get('/docs', [SharedController::class, 'docs'])->name('docs');
    Route::get('/help', [SharedController::class, 'help'])->name('help');
    Route::get('/support', [SharedController::class, 'support'])->name('support');
    Route::get('/resources', [SharedController::class, 'resources'])->name('resources');
    Route::get('/parent-guide', [SharedController::class, 'parentGuide'])->name('parent-guide');

    // Temporary backward-compatible enrollment routes (redirect to role-specific routes)
    Route::middleware('role:guardian')->group(function () {
        Route::resource('enrollments', GuardianEnrollmentController::class);
    });
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Authenticated + Verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Super Admin Routes
    Route::prefix('super-admin')->name('super-admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // Users Management
        Route::resource('users', SuperAdminUserController::class);

        // Students Management
        Route::get('/students/{student}/enrollments', [SuperAdminStudentController::class, 'enrollments'])->name('students.enrollments');
        Route::resource('students', SuperAdminStudentController::class);

        // Guardians Management
        Route::resource('guardians', SuperAdminGuardianController::class);

        // Enrollments Management
        Route::resource('enrollments', SuperAdminEnrollmentController::class);
        Route::post('/enrollments/{enrollment}/approve', [SuperAdminEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [SuperAdminEnrollmentController::class, 'reject'])->name('enrollments.reject');
        Route::get('/enrollments/{enrollment}/certificate', [SuperAdminEnrollmentController::class, 'downloadCertificate'])->name('enrollments.certificate');
        Route::get('/enrollments/{enrollment}/payment-history', [SuperAdminEnrollmentController::class, 'downloadPaymentHistory'])->name('enrollments.payment-history');

        // Invoices Management
        Route::resource('invoices', SuperAdminInvoiceController::class);
        Route::get('/invoices/{invoice}/download', [SuperAdminInvoiceController::class, 'download'])->name('invoices.download');

        // Payments Management
        Route::resource('payments', SuperAdminPaymentController::class);
        Route::post('/payments/{payment}/refund', [SuperAdminPaymentController::class, 'refund'])->name('payments.refund');

        // Receipts Management
        Route::resource('receipts', SuperAdminReceiptController::class);

        // Documents Management
        Route::resource('documents', SuperAdminDocumentController::class)->only(['index', 'show', 'destroy']);
        Route::get('/documents/{document}/view', [SuperAdminDocumentController::class, 'view'])->name('documents.view');
        Route::get('/documents/{document}/download', [SuperAdminDocumentController::class, 'download'])->name('documents.download');
        Route::post('/documents/{document}/verify', [SuperAdminDocumentController::class, 'verify'])->name('documents.verify');
        Route::post('/documents/{document}/reject', [SuperAdminDocumentController::class, 'reject'])->name('documents.reject');

        // Grade Level Fees Management
        Route::delete('/grade-level-fees', [SuperAdminGradeLevelFeeController::class, 'destroy'])->name('grade-level-fees.bulk-destroy');
        Route::resource('grade-level-fees', SuperAdminGradeLevelFeeController::class);
        Route::post('/grade-level-fees/{gradeLevelFee}/duplicate', [SuperAdminGradeLevelFeeController::class, 'duplicate'])->name('grade-level-fees.duplicate');

        // Enrollment Periods Management
        Route::resource('enrollment-periods', SuperAdminEnrollmentPeriodController::class);
        Route::post('/enrollment-periods/{enrollmentPeriod}/activate', [SuperAdminEnrollmentPeriodController::class, 'activate'])->name('enrollment-periods.activate');
        Route::post('/enrollment-periods/{enrollmentPeriod}/close', [SuperAdminEnrollmentPeriodController::class, 'close'])->name('enrollment-periods.close');

        // School Years Management
        Route::resource('school-years', SuperAdminSchoolYearController::class);
        Route::post('/school-years/{schoolYear}/set-active', [SuperAdminSchoolYearController::class, 'setActive'])->name('school-years.set-active');

        // Audit Log Management
        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [SuperAdminAuditLogController::class, 'index'])->name('index');
            Route::get('/{activity}', [SuperAdminAuditLogController::class, 'show'])->name('show');
            Route::post('/export', [SuperAdminAuditLogController::class, 'export'])->name('export');
        });

        // School Information Management
        Route::prefix('school-information')->name('school-information.')->group(function () {
            Route::get('/', [SuperAdminSchoolInformationController::class, 'index'])->name('index');
            Route::put('/', [SuperAdminSchoolInformationController::class, 'update'])->name('update');
        });

        // Settings Management
        Route::resource('settings', SuperAdminSettingController::class);
    });

    // Admin Routes (for administrator roles)
    Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Enrollments Management
        Route::resource('enrollments', AdminEnrollmentController::class);
        Route::get('/enrollments/{enrollment}/certificate', [AdminEnrollmentController::class, 'downloadCertificate'])->name('enrollments.certificate');
        Route::get('/enrollments/{enrollment}/payment-history', [AdminEnrollmentController::class, 'downloadPaymentHistory'])->name('enrollments.payment-history');

        // Students Management
        Route::resource('students', AdminStudentController::class);

        // Users Management (limited compared to super-admin)
        Route::resource('users', AdminUserController::class);

        // Payments Management
        Route::resource('payments', AdminPaymentController::class);

        // Grade Level Fees Management
        Route::resource('grade-level-fees', AdminGradeLevelFeeController::class);
        Route::post('/grade-level-fees/{gradeLevelFee}/duplicate', [AdminGradeLevelFeeController::class, 'duplicate'])->name('grade-level-fees.duplicate');

        // Enrollment Periods Management
        Route::resource('enrollment-periods', AdminEnrollmentPeriodController::class);
        Route::post('/enrollment-periods/{enrollmentPeriod}/activate', [AdminEnrollmentPeriodController::class, 'activate'])->name('enrollment-periods.activate');
        Route::post('/enrollment-periods/{enrollmentPeriod}/close', [AdminEnrollmentPeriodController::class, 'close'])->name('enrollment-periods.close');

        // Document Management
        Route::get('/documents/pending', [AdminDocumentController::class, 'pending'])->name('documents.pending');
        Route::get('/documents/{document}', [AdminDocumentController::class, 'show'])->name('documents.show');
        Route::get('/documents/{document}/view', [AdminDocumentController::class, 'view'])->name('documents.view');
        Route::get('/documents/{document}/download', [AdminDocumentController::class, 'download'])->name('documents.download');
        Route::post('/documents/{document}/verify', [AdminDocumentController::class, 'verify'])->name('documents.verify');
        Route::post('/documents/{document}/reject', [AdminDocumentController::class, 'reject'])->name('documents.reject');

        // Reports Management
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/enrollment-statistics', [AdminReportController::class, 'enrollmentStatistics'])->name('reports.enrollment-statistics');
        Route::get('/reports/student-demographics', [AdminReportController::class, 'studentDemographics'])->name('reports.student-demographics');
        Route::get('/reports/class-roster', [AdminReportController::class, 'classRoster'])->name('reports.class-roster');
        Route::get('/reports/filter-options', [AdminReportController::class, 'filterOptions'])->name('reports.filter-options');

        // Audit Logs Management
        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
            Route::get('/{activity}', [AdminAuditLogController::class, 'show'])->name('show');
            Route::post('/export', [AdminAuditLogController::class, 'export'])->name('export');
        });

        // School Information Management
        Route::prefix('school-information')->name('school-information.')->group(function () {
            Route::get('/', [AdminSchoolInformationController::class, 'index'])->name('index');
            Route::put('/', [AdminSchoolInformationController::class, 'update'])->name('update');
        });

        // Invoice Management
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class)->only(['index', 'show']);
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Admin\InvoiceController::class, 'download'])->name('invoices.download');
    });

    // Registrar Routes
    Route::prefix('registrar')->name('registrar.')->middleware('role:registrar|administrator|super_admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [RegistrarDashboardController::class, 'index'])->name('dashboard');

        // Students Management
        Route::get('/students/export', [RegistrarStudentController::class, 'export'])->name('students.export');
        Route::resource('students', RegistrarStudentController::class);

        // Enrollments Management
        Route::resource('enrollments', RegistrarEnrollmentController::class)->only(['index', 'show']);
        Route::post('/enrollments/{enrollment}/approve', [RegistrarEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [RegistrarEnrollmentController::class, 'reject'])->name('enrollments.reject');
        Route::post('/enrollments/{enrollment}/request-info', [RegistrarEnrollmentController::class, 'requestInfo'])->name('enrollments.request-info');
        Route::post('/enrollments/{enrollment}/complete', [RegistrarEnrollmentController::class, 'complete'])->name('enrollments.complete');
        Route::post('/enrollments/{enrollment}/confirm-payment', [RegistrarEnrollmentController::class, 'confirmPayment'])->name('enrollments.confirm-payment');
        Route::put('/enrollments/{enrollment}/payment-status', [RegistrarEnrollmentController::class, 'updatePaymentStatus'])->name('enrollments.update-payment-status');
        Route::put('/enrollments/{enrollment}/status', [RegistrarEnrollmentController::class, 'updateStatus'])->name('enrollments.update-status');
        Route::post('/enrollments/bulk-approve', [RegistrarEnrollmentController::class, 'bulkApprove'])->name('enrollments.bulk-approve');
        Route::get('/enrollments/export', [RegistrarEnrollmentController::class, 'export'])->name('enrollments.export');
        Route::get('/enrollments/{enrollment}/certificate', [RegistrarEnrollmentController::class, 'downloadCertificate'])->name('enrollments.certificate');
        Route::get('/enrollments/{enrollment}/payment-history', [RegistrarEnrollmentController::class, 'downloadPaymentHistory'])->name('enrollments.payment-history');

        // Quick actions for dashboard
        Route::post('/enrollments/{enrollment}/quick-approve', [RegistrarDashboardController::class, 'quickApprove'])->name('enrollments.quick-approve');
        Route::post('/enrollments/{enrollment}/quick-reject', [RegistrarDashboardController::class, 'quickReject'])->name('enrollments.quick-reject');

        // Grade Level Fees Management
        Route::resource('grade-level-fees', RegistrarGradeLevelFeeController::class);
        Route::post('/grade-level-fees/{gradeLevelFee}/duplicate', [RegistrarGradeLevelFeeController::class, 'duplicate'])->name('grade-level-fees.duplicate');

        // Document Management
        Route::get('/documents/pending', [RegistrarDocumentController::class, 'pending'])->name('documents.pending');
        Route::get('/documents/{document}', [RegistrarDocumentController::class, 'show'])->name('documents.show');
        Route::get('/documents/{document}/view', [RegistrarDocumentController::class, 'view'])->name('documents.view');
        Route::get('/documents/{document}/download', [RegistrarDocumentController::class, 'download'])->name('documents.download');
        Route::post('/documents/{document}/verify', [RegistrarDocumentController::class, 'verify'])->name('documents.verify');
        Route::post('/documents/{document}/reject', [RegistrarDocumentController::class, 'reject'])->name('documents.reject');

        // Invoice Management
        Route::resource('invoices', \App\Http\Controllers\Registrar\InvoiceController::class)->only(['index', 'show']);
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Registrar\InvoiceController::class, 'download'])->name('invoices.download');
    });

    // Guardian Routes
    Route::prefix('guardian')->name('guardian.')->middleware('role:guardian')->group(function () {
        // Dashboard
        Route::get('/dashboard', [GuardianDashboardController::class, 'index'])->name('dashboard');

        // Students Management
        Route::resource('students', GuardianStudentController::class);

        // Enrollments Management
        Route::resource('enrollments', GuardianEnrollmentController::class);
        Route::get('/enrollments/{enrollment}/payment-history-pdf', [GuardianEnrollmentController::class, 'downloadPaymentHistory'])->name('enrollments.payment-history-pdf');
        Route::get('/enrollments/{enrollment}/certificate', [GuardianEnrollmentController::class, 'downloadCertificate'])->name('enrollments.certificate');
        Route::post('/enrollments/{enrollment}/respond-to-info-request', [GuardianEnrollmentController::class, 'respondToInfoRequest'])->name('enrollments.respond-to-info-request');

        // Billing Information
        Route::get('/billing', [GuardianBillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/{enrollment}', [GuardianBillingController::class, 'show'])->name('billing.show');

        // Receipt Management
        Route::get('/receipts', [\App\Http\Controllers\Guardian\ReceiptController::class, 'index'])->name('receipts.index');
        Route::get('/receipts/{receipt}', [\App\Http\Controllers\Guardian\ReceiptController::class, 'show'])->name('receipts.show');

        // Invoice Management
        Route::resource('invoices', \App\Http\Controllers\Guardian\InvoiceController::class)->only(['index', 'show']);
        Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Guardian\InvoiceController::class, 'download'])->name('invoices.download');

        // Payment Management
        Route::get('/payments', [\App\Http\Controllers\Guardian\PaymentController::class, 'index'])->name('payments.index');

        // Document Management
        Route::get('/students/{student}/documents', [\App\Http\Controllers\Guardian\DocumentController::class, 'index'])->name('students.documents.index');
        Route::post('/students/{student}/documents', [\App\Http\Controllers\Guardian\DocumentController::class, 'store'])->name('students.documents.store')->middleware('throttle:document-uploads');
        Route::get('/students/{student}/documents/{document}', [\App\Http\Controllers\Guardian\DocumentController::class, 'show'])->name('students.documents.show');
        Route::get('/students/{student}/documents/{document}/download', [\App\Http\Controllers\Guardian\DocumentController::class, 'download'])->name('students.documents.download');
        Route::delete('/students/{student}/documents/{document}', [\App\Http\Controllers\Guardian\DocumentController::class, 'destroy'])->name('students.documents.destroy');
    });

    // Student dashboard
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/enrollment-period/active', [\App\Http\Controllers\Api\EnrollmentPeriodController::class, 'active'])->name('enrollment-period.active');
});

/*
|--------------------------------------------------------------------------
| Include other route files
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
