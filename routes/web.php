<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('landing');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/enrollment', function () {
    return Inertia::render('enrollment');
})->name('enrollment');

Route::get('/invoice', [InvoiceController::class, 'latest'])
    ->middleware('auth')
    ->name('invoice');

Route::get('/invoice/{invoice}', [InvoiceController::class, 'show'])
    ->middleware('auth')
    ->name('invoice.show');

Route::get('/profilesettings', function () {
    return Inertia::render('profilesettings');
})->name('profilesettings');

Route::get('/registrar', function () {
    return Inertia::render('registrar');
})->name('registrar');

Route::get('/application', function () {
    return Inertia::render('application');
})->name('application');

Route::get('/studentreport', function () {
    return Inertia::render('studentreport');
})->name('studentreport');

Route::get('/tuition', [BillingController::class, 'tuition'])
    ->middleware('auth')
    ->name('tuition');

Route::put('/billing/payment/{enrollmentId}', [BillingController::class, 'updatePayment'])
    ->middleware('auth')
    ->name('billing.updatePayment');

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin dashboards (for super_admin and administrator roles)
    Route::get('admin/dashboard', function () {
        return Inertia::render('admin/dashboard');
    })->middleware('role:super_admin|administrator')->name('admin.dashboard');

    // Registrar dashboard
    Route::get('registrar/dashboard', function () {
        return Inertia::render('registrar/dashboard');
    })->middleware('role:registrar')->name('registrar.dashboard');

    // Guardian dashboard
    Route::get('guardian/dashboard', function () {
        return Inertia::render('guardian/dashboard');
    })->middleware('role:guardian')->name('guardian.dashboard');

    // Student dashboard
    Route::get('student/dashboard', function () {
        return Inertia::render('student/dashboard');
    })->middleware('role:student')->name('student.dashboard');

    // Guardian routes for managing students
    Route::middleware('role:guardian')->prefix('guardian')->name('guardian.')->group(function () {
        Route::resource('students', StudentController::class);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
