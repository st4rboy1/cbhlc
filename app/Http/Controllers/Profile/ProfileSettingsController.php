<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ProfileSettingsController extends Controller
{
    /**
     * Display the profile settings page.
     */
    public function index()
    {
        return Inertia::render('profilesettings');
    }
}
