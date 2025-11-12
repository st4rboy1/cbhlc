<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display a listing of reports.
     */
    public function index()
    {
        return Inertia::render('super-admin/reports/index');
    }
}
