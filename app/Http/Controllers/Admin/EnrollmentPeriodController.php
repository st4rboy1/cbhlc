<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class EnrollmentPeriodController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/enrollment-periods/index');
    }
}
