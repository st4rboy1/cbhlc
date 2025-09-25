<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index()
    {
        return Inertia::render('public/landing');
    }
}
