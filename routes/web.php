<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GuardianDashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StudentController;
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
    | Enrollment Management
    |--------------------------------------------------------------------------
    */
    // Enrollment Routes - Full resource controller
    Route::resource('enrollments', EnrollmentController::class);

    /*
    |--------------------------------------------------------------------------
    | Financial Management
    |--------------------------------------------------------------------------
    */
    // Invoice Routes - Using resource controller (only index and show actions)
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);

    // Tuition Routes
    Route::get('/tuition', [TuitionController::class, 'index'])->name('tuition');

    // Billing Routes
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::put('/payment/{enrollmentId}', [BillingController::class, 'updatePayment'])->name('updatePayment');
    });

    /*
    |--------------------------------------------------------------------------
    | Academic Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/students/{student}/report', [StudentReportController::class, 'show'])
        ->name('students.report');
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

    // Registrar dashboard
    Route::prefix('registrar')->name('registrar.')->middleware('role:registrar')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('registrar/dashboard');
        })->name('dashboard');
    });

    // Guardian routes
    Route::prefix('guardian')->name('guardian.')->middleware('role:guardian')->group(function () {
        Route::get('/dashboard', [GuardianDashboardController::class, 'index'])->name('dashboard');

        // Guardian resource routes for managing students
        Route::resource('students', StudentController::class);
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
