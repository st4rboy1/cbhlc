<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    /**
     * Display the application information page.
     */
    public function index()
    {
        return Inertia::render('public/application');
    }
}
