<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Guardian\BillingController as GuardianBillingController;
use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\Guardian\EnrollmentController as GuardianEnrollmentController;
use App\Http\Controllers\Guardian\StudentController as GuardianStudentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ApplicationController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Registrar\DashboardController as RegistrarDashboardController;
use App\Http\Controllers\Registrar\EnrollmentController as RegistrarEnrollmentController;
use App\Http\Controllers\Registrar\GradeLevelFeeController as RegistrarGradeLevelFeeController;
use App\Http\Controllers\Registrar\StudentController as RegistrarStudentController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\EnrollmentController as SuperAdminEnrollmentController;
use App\Http\Controllers\SuperAdmin\GradeLevelFeeController as SuperAdminGradeLevelFeeController;
use App\Http\Controllers\SuperAdmin\GuardianController as SuperAdminGuardianController;
use App\Http\Controllers\SuperAdmin\InvoiceController as SuperAdminInvoiceController;
use App\Http\Controllers\SuperAdmin\PaymentController as SuperAdminPaymentController;
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

Route::get('/registrar', [RegistrarInfoController::class, 'index'])->name('registrar');

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
    | Shared Routes (Temporarily - will be refactored per role)
    |--------------------------------------------------------------------------
    */
    // Invoice Routes - Using resource controller (only index and show actions)
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);

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
        Route::resource('students', SuperAdminStudentController::class);

        // Guardians Management
        Route::resource('guardians', SuperAdminGuardianController::class);

        // Enrollments Management
        Route::resource('enrollments', SuperAdminEnrollmentController::class);
        Route::post('/enrollments/{enrollment}/approve', [SuperAdminEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [SuperAdminEnrollmentController::class, 'reject'])->name('enrollments.reject');

        // Invoices Management
        Route::resource('invoices', SuperAdminInvoiceController::class);

        // Payments Management
        Route::resource('payments', SuperAdminPaymentController::class);
        Route::post('/payments/{payment}/refund', [SuperAdminPaymentController::class, 'refund'])->name('payments.refund');

        // Grade Level Fees Management
        Route::resource('grade-level-fees', SuperAdminGradeLevelFeeController::class);
    });

    // Admin Routes (for administrator roles)
    Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Enrollments Management
        Route::resource('enrollments', AdminEnrollmentController::class);

        // Students Management
        Route::resource('students', AdminStudentController::class);

        // Users Management (limited compared to super-admin)
        Route::resource('users', AdminUserController::class);
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
        Route::post('/enrollments/{enrollment}/complete', [RegistrarEnrollmentController::class, 'complete'])->name('enrollments.complete');
        Route::post('/enrollments/{enrollment}/confirm-payment', [RegistrarEnrollmentController::class, 'confirmPayment'])->name('enrollments.confirm-payment');
        Route::put('/enrollments/{enrollment}/payment-status', [RegistrarEnrollmentController::class, 'updatePaymentStatus'])->name('enrollments.update-payment-status');
        Route::post('/enrollments/bulk-approve', [RegistrarEnrollmentController::class, 'bulkApprove'])->name('enrollments.bulk-approve');
        Route::get('/enrollments/export', [RegistrarEnrollmentController::class, 'export'])->name('enrollments.export');

        // Quick actions for dashboard
        Route::post('/enrollments/{enrollment}/quick-approve', [RegistrarDashboardController::class, 'quickApprove'])->name('enrollments.quick-approve');
        Route::post('/enrollments/{enrollment}/quick-reject', [RegistrarDashboardController::class, 'quickReject'])->name('enrollments.quick-reject');

        // Grade Level Fees Management
        Route::resource('grade-level-fees', RegistrarGradeLevelFeeController::class);
        Route::post('/grade-level-fees/{gradeLevelFee}/duplicate', [RegistrarGradeLevelFeeController::class, 'duplicate'])->name('grade-level-fees.duplicate');
    });

    // Guardian Routes
    Route::prefix('guardian')->name('guardian.')->middleware('role:guardian')->group(function () {
        // Dashboard
        Route::get('/dashboard', [GuardianDashboardController::class, 'index'])->name('dashboard');

        // Students Management
        Route::resource('students', GuardianStudentController::class);

        // Enrollments Management
        Route::resource('enrollments', GuardianEnrollmentController::class);

        // Billing Information
        Route::get('/billing', [GuardianBillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/{enrollment}', [GuardianBillingController::class, 'show'])->name('billing.show');

        // Document Management
        Route::get('/students/{student}/documents', [\App\Http\Controllers\Guardian\DocumentController::class, 'index'])->name('students.documents.index');
        Route::post('/students/{student}/documents', [\App\Http\Controllers\Guardian\DocumentController::class, 'store'])->name('students.documents.store');
        Route::get('/students/{student}/documents/{document}', [\App\Http\Controllers\Guardian\DocumentController::class, 'show'])->name('students.documents.show');
        Route::delete('/students/{student}/documents/{document}', [\App\Http\Controllers\Guardian\DocumentController::class, 'destroy'])->name('students.documents.destroy');
    });

    // Student dashboard
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Include other route files
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
