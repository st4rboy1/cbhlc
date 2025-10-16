<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolInformationController extends Controller
{
    /**
     * Display school information settings
     */
    public function index(): Response
    {
        $information = SchoolInformation::getGrouped();

        return Inertia::render('super-admin/school-information/index', [
            'information' => $information,
        ]);
    }

    /**
     * Update school information
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:school_information,id',
            'updates.*.value' => 'nullable|string',
        ]);

        foreach ($validated['updates'] as $update) {
            SchoolInformation::where('id', $update['id'])
                ->update(['value' => $update['value']]);
        }

        return redirect()->back()->with('success', 'School information updated successfully.');
    }
}
