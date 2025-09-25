<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppearanceController extends Controller
{
    /**
     * Display the appearance settings page.
     */
    public function index()
    {
        return Inertia::render('settings/appearance');
    }

    /**
     * Update appearance settings.
     */
    public function update(Request $request)
    {
        // Handle appearance update logic
        session(['appearance' => $request->appearance]);

        return back()->with('success', 'Appearance settings updated successfully.');
    }
}
