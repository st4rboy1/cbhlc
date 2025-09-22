<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\GuardianDashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StudentController;
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

Route::get('/enrollment', function () {
    return Inertia::render('enrollment');
})->name('enrollment');

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
    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/settings', function () {
            return Inertia::render('profilesettings');
        })->name('settings');
    });

    // Invoice Routes
    Route::prefix('invoice')->name('invoice.')->group(function () {
        Route::get('/', [InvoiceController::class, 'latest'])->name('index');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    });

    // Tuition Routes
    Route::get('/tuition', [TuitionController::class, 'index'])->name('tuition');

    // Billing Routes
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::put('/payment/{enrollmentId}', [BillingController::class, 'updatePayment'])->name('updatePayment');
    });

    // Reports
    Route::get('/studentreport', function () {
        return Inertia::render('studentreport');
    })->name('studentreport');
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
