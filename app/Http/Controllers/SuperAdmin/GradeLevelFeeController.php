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

        $query = GradeLevelFee::with(['enrollmentPeriod.schoolYear']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('grade_level', 'like', "%{$search}%");
        }

        // Filter by school year (via enrollment period)
        if ($request->filled('school_year_id')) {
            $schoolYearId = $request->get('school_year_id');
            $query->whereHas('enrollmentPeriod', function ($q) use ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            });
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('is_active', $request->get('active') === 'true');
        }

        $fees = $query->latest()->paginate(15)->withQueryString();

        // Get school years filtered by active and upcoming status only (no past years)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('super-admin/grade-level-fees/index', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'school_year_id', 'active']),
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
            'school_year_id' => ['required', 'exists:school_years,id'],
        ]);

        // Get or create enrollment period for the target school year
        $enrollmentPeriod = \App\Models\EnrollmentPeriod::firstOrCreate(
            ['school_year_id' => $validated['school_year_id']],
            [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'status' => 'upcoming',
            ]
        );

        // Check if fee already exists for the target enrollment period and grade level
        $exists = GradeLevelFee::where('enrollment_period_id', $enrollmentPeriod->id)
            ->where('grade_level', $gradeLevelFee->grade_level)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Fee already exists for this grade level in the specified school year.');
        }

        // Create a copy with new enrollment period
        $newFee = $gradeLevelFee->replicate();
        $newFee->enrollment_period_id = $enrollmentPeriod->id;
        $newFee->save();

        $schoolYear = \App\Models\SchoolYear::find($validated['school_year_id']);

        return redirect()->route('super-admin.grade-level-fees.index')
            ->with('success', 'Grade level fee duplicated successfully for school year '.$schoolYear->name);
    }
}
