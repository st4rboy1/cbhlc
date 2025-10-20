<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class GradeLevelFeeController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/grade-level-fees/index');
    }
}
