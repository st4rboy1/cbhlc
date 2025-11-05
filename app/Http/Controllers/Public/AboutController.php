<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Inertia\Inertia;

class AboutController extends Controller
{
    /**
     * Display the about page.
     */
    public function index()
    {
        return Inertia::render('public/about', [
            'schoolInformation' => SchoolInformation::getGrouped(),
        ]);
    }
}
