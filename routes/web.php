<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/index', function () {
    return Inertia::render('index');
})->name('index');

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
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
