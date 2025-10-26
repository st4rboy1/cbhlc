<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSchoolYearRequest;
use App\Http\Requests\SuperAdmin\UpdateSchoolYearRequest;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SchoolYear::class);

        $query = SchoolYear::query()->withCount(['enrollments', 'invoices']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('start_year', 'like', "%{$search}%")
                    ->orWhere('end_year', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $schoolYears = $query->latest('start_year')->paginate(15)->withQueryString();

        $activeSchoolYear = SchoolYear::active();

        return Inertia::render('super-admin/school-years/index', [
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', SchoolYear::class);

        return Inertia::render('super-admin/school-years/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolYearRequest $request)
    {
        Gate::authorize('create', SchoolYear::class);

        $validated = $request->validated();

        $schoolYear = SchoolYear::create($validated);

        activity()
            ->performedOn($schoolYear)
            ->withProperties($schoolYear->toArray())
            ->log('School year created');

        return redirect()->route('super-admin.school-years.index')
            ->with('success', 'School year created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolYear $schoolYear)
    {
        Gate::authorize('view', $schoolYear);

        $schoolYear->loadCount(['enrollments', 'invoices']);
        $schoolYear->load(['enrollments' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('super-admin/school-years/show', [
            'schoolYear' => $schoolYear,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolYear $schoolYear)
    {
        Gate::authorize('update', $schoolYear);

        return Inertia::render('super-admin/school-years/edit', [
            'schoolYear' => $schoolYear,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear)
    {
        Gate::authorize('update', $schoolYear);

        $validated = $request->validated();

        $oldData = $schoolYear->toArray();
        $schoolYear->update($validated);

        activity()
            ->performedOn($schoolYear)
            ->withProperties(['old' => $oldData, 'new' => $schoolYear->fresh()->toArray()])
            ->log('School year updated');

        return redirect()->route('super-admin.school-years.index')
            ->with('success', 'School year updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolYear $schoolYear)
    {
        Gate::authorize('delete', $schoolYear);

        // Check if school year has enrollments
        if ($schoolYear->enrollments()->exists()) {
            return redirect()->route('super-admin.school-years.index')
                ->with('error', 'Cannot delete school year with existing enrollments.');
        }

        $schoolYearData = $schoolYear->toArray();
        $schoolYear->delete();

        activity()
            ->withProperties($schoolYearData)
            ->log('School year deleted');

        return redirect()->route('super-admin.school-years.index')
            ->with('success', 'School year deleted successfully.');
    }

    /**
     * Set the specified school year as active.
     */
    public function setActive(SchoolYear $schoolYear)
    {
        Gate::authorize('update', $schoolYear);

        $schoolYear->setAsActive();

        activity()
            ->performedOn($schoolYear)
            ->log('School year set as active');

        return redirect()->route('super-admin.school-years.index')
            ->with('success', 'School year set as active successfully.');
    }
}
