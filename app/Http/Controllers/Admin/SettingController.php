<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSettingRequest;
use App\Http\Requests\SuperAdmin\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display a listing of settings.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Setting::class);

        $query = Setting::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $settings = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('admin/settings/index', [
            'settings' => $settings,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        Gate::authorize('create', Setting::class);

        return Inertia::render('admin/settings/create');
    }

    /**
     * Store a newly created setting.
     */
    public function store(StoreSettingRequest $request)
    {
        Gate::authorize('create', Setting::class);

        $validated = $request->validated();
        $setting = Setting::create($validated);

        activity()
            ->performedOn($setting)
            ->withProperties($setting->toArray())
            ->log('Setting created');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Display the specified setting.
     */
    public function show(Setting $setting)
    {
        Gate::authorize('view', $setting);

        return Inertia::render('admin/settings/show', [
            'setting' => $setting,
        ]);
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit(Setting $setting)
    {
        Gate::authorize('update', $setting);

        return Inertia::render('admin/settings/edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update the specified setting.
     */
    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        Gate::authorize('update', $setting);

        $validated = $request->validated();
        $oldData = $setting->toArray();
        $setting->update($validated);

        activity()
            ->performedOn($setting)
            ->withProperties(['old' => $oldData, 'new' => $setting->fresh()->toArray()])
            ->log('Setting updated');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(Setting $setting)
    {
        Gate::authorize('delete', $setting);

        $settingData = $setting->toArray();
        $setting->delete();

        activity()
            ->withProperties($settingData)
            ->log('Setting deleted');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
}
