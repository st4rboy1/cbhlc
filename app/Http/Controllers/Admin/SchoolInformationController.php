<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SchoolInformationController extends Controller
{
    /**
     * Display school information settings
     */
    public function index(): Response
    {
        $values = SchoolInformation::getAllCached()
            ->pluck('value', 'key')
            ->toArray();

        return Inertia::render('admin/school-information/index', [
            'values' => $values,
        ]);
    }

    /**
     * Update school information
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => 'nullable|string|max:255',
            'school_email' => 'nullable|email|max:255',
            'school_phone' => 'nullable|string|max:50',
            'school_mobile' => 'nullable|string|max:50',
            'school_address' => 'nullable|string|max:500',
            'office_hours_weekday' => 'nullable|string|max:255',
            'office_hours_saturday' => 'nullable|string|max:255',
            'office_hours_sunday' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'school_tagline' => 'nullable|string|max:255',
            'school_description' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            SchoolInformation::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Manually clear the cache to ensure the changes are reflected immediately
        Cache::forget('school_information');

        return redirect()->back()->with('success', 'School information updated successfully.');
    }
}
