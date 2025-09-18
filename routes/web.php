<?php

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

Route::get('/invoice', function () {
    return Inertia::render('invoice');
})->name('invoice');

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

Route::get('/tuition', function () {
    return Inertia::render('tuition');
})->name('tuition');

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin dashboards (for super_admin and administrator roles)
    Route::get('admin/dashboard', function () {
        return Inertia::render('admin/dashboard');
    })->name('admin.dashboard');

    // Registrar dashboard
    Route::get('registrar/dashboard', function () {
        return Inertia::render('registrar/dashboard');
    })->name('registrar.dashboard');

    // Parent dashboard
    Route::get('parent/dashboard', function () {
        return Inertia::render('parent/dashboard');
    })->name('parent.dashboard');

    // Student dashboard
    Route::get('student/dashboard', function () {
        return Inertia::render('student/dashboard');
    })->name('student.dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
