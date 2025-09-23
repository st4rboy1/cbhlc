<?php

// Guardian Controllers
use App\Http\Controllers\Guardian\BillingController as GuardianBillingController;
use App\Http\Controllers\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\Guardian\EnrollmentController as GuardianEnrollmentController;
use App\Http\Controllers\Guardian\StudentController as GuardianStudentController;
// Registrar Controllers
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Registrar\DashboardController as RegistrarDashboardController;
use App\Http\Controllers\Registrar\EnrollmentController as RegistrarEnrollmentController;
// Shared Controllers (to be refactored later)
use App\Http\Controllers\Registrar\StudentController as RegistrarStudentController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\TuitionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('landing');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/application', function () {
    return Inertia::render('application');
})->name('application');

Route::get('/registrar', function () {
    return Inertia::render('registrar');
})->name('registrar');

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
        Route::get('/settings', function () {
            return Inertia::render('profilesettings');
        })->name('settings');
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
    // Admin dashboards (for super_admin and administrator roles)
    Route::prefix('admin')->name('admin.')->middleware('role:super_admin|administrator')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('admin/dashboard');
        })->name('dashboard');
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
        Route::put('/enrollments/{enrollment}/payment-status', [RegistrarEnrollmentController::class, 'updatePaymentStatus'])->name('enrollments.update-payment-status');
        Route::post('/enrollments/bulk-approve', [RegistrarEnrollmentController::class, 'bulkApprove'])->name('enrollments.bulk-approve');
        Route::get('/enrollments/export', [RegistrarEnrollmentController::class, 'export'])->name('enrollments.export');

        // Quick actions for dashboard
        Route::post('/enrollments/{enrollment}/quick-approve', [RegistrarDashboardController::class, 'quickApprove'])->name('enrollments.quick-approve');
        Route::post('/enrollments/{enrollment}/quick-reject', [RegistrarDashboardController::class, 'quickReject'])->name('enrollments.quick-reject');
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
    });

    // Student dashboard
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('student/dashboard');
        })->name('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Include other route files
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
