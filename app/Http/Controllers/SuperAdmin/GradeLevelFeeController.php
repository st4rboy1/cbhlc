<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreGradeLevelFeeRequest;
use App\Http\Requests\SuperAdmin\UpdateGradeLevelFeeRequest;
use App\Models\GradeLevelFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GradeLevelFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', GradeLevelFee::class);

        $query = GradeLevelFee::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('grade_level', 'like', "%{$search}%");
        }

        // Filter by school year
        if ($request->filled('school_year')) {
            $query->where('school_year', $request->get('school_year'));
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('is_active', $request->get('active') === 'true');
        }

        $fees = $query->latest()->paginate(15)->withQueryString();

        // Get all unique school years from grade level fees
        $schoolYears = \App\Models\SchoolYear::orderBy('start_year', 'desc')->get();

        return Inertia::render('super-admin/grade-level-fees/index', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'school_year', 'active']),
            'gradeLevels' => \App\Enums\GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', GradeLevelFee::class);

        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('super-admin/grade-level-fees/create', [
            'gradeLevels' => \App\Enums\GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGradeLevelFeeRequest $request)
    {
        Gate::authorize('create', GradeLevelFee::class);

        $validated = $request->validated();

        // Get school year and populate school_year string for backward compatibility
        $schoolYear = \App\Models\SchoolYear::findOrFail($validated['school_year_id']);
        $validated['school_year'] = $schoolYear->name;

        GradeLevelFee::create($validated);

        return redirect()->route('super-admin.grade-level-fees.index')
            ->with('success', 'Grade level fee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('view', $gradeLevelFee);

        return Inertia::render('super-admin/grade-level-fees/show', [
            'fee' => $gradeLevelFee,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('update', $gradeLevelFee);

        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('super-admin/grade-level-fees/edit', [
            'fee' => $gradeLevelFee,
            'gradeLevels' => \App\Enums\GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGradeLevelFeeRequest $request, GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('update', $gradeLevelFee);

        $validated = $request->validated();

        // Get school year and populate school_year string for backward compatibility
        $schoolYear = \App\Models\SchoolYear::findOrFail($validated['school_year_id']);
        $validated['school_year'] = $schoolYear->name;

        $gradeLevelFee->update($validated);

        return redirect()->route('super-admin.grade-level-fees.index')
            ->with('success', 'Grade level fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('delete', $gradeLevelFee);

        $gradeLevelFee->delete();

        return redirect()->route('super-admin.grade-level-fees.index')
            ->with('success', 'Grade level fee deleted successfully.');
    }

    /**
     * Duplicate a grade level fee for a different school year.
     */
    public function duplicate(Request $request, GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('create', GradeLevelFee::class);

        $validated = $request->validate([
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
        ]);

        // Check if fee already exists for the target school year and grade level
        $exists = GradeLevelFee::where('school_year', $validated['school_year'])
            ->where('grade_level', $gradeLevelFee->grade_level)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Fee already exists for this grade level in the specified school year.');
        }

        // Create a copy with new school year
        $newFee = $gradeLevelFee->replicate();
        $newFee->school_year = $validated['school_year'];
        $newFee->save();

        return redirect()->route('super-admin.grade-level-fees.index')
            ->with('success', 'Grade level fee duplicated successfully for school year '.$validated['school_year']);
    }
}
