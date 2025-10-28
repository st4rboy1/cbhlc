<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreGradeLevelFeeRequest;
use App\Http\Requests\SuperAdmin\UpdateGradeLevelFeeRequest;
use App\Models\GradeLevelFee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeLevelFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if user has permission to view fees
        if (! auth()->user()->can('grade_level_fees.view')) {
            abort(403, 'Unauthorized to view grade level fees');
        }

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

        return Inertia::render('registrar/grade-level-fees/index', [
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
        // Check if user has permission to create fees
        if (! auth()->user()->can('grade_level_fees.create')) {
            abort(403, 'Unauthorized to create grade level fees');
        }

        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('registrar/grade-level-fees/create', [
            'gradeLevels' => GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGradeLevelFeeRequest $request)
    {
        // Check if user has permission to create fees
        if (! auth()->user()->can('grade_level_fees.create')) {
            abort(403, 'Unauthorized to create grade level fees');
        }

        $validated = $request->validated();

        // Set created_by to track who created the fee
        $validated['created_by'] = auth()->id();

        GradeLevelFee::create($validated);

        return redirect()->route('registrar.grade-level-fees.index')
            ->with('success', 'Grade level fee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GradeLevelFee $gradeLevelFee)
    {
        // Check if user has permission to view fees
        if (! auth()->user()->can('grade_level_fees.view')) {
            abort(403, 'Unauthorized to view grade level fees');
        }

        return Inertia::render('registrar/grade-level-fees/show', [
            'fee' => $gradeLevelFee,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GradeLevelFee $gradeLevelFee)
    {
        // Check if user has permission to update fees
        if (! auth()->user()->can('grade_level_fees.update')) {
            abort(403, 'Unauthorized to update grade level fees');
        }

        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('registrar/grade-level-fees/edit', [
            'fee' => $gradeLevelFee,
            'gradeLevels' => GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGradeLevelFeeRequest $request, GradeLevelFee $gradeLevelFee)
    {
        // Check if user has permission to update fees
        if (! auth()->user()->can('grade_level_fees.update')) {
            abort(403, 'Unauthorized to update grade level fees');
        }

        $validated = $request->validated();

        // Set updated_by to track who updated the fee
        $validated['updated_by'] = auth()->id();

        $gradeLevelFee->update($validated);

        return redirect()->route('registrar.grade-level-fees.index')
            ->with('success', 'Grade level fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GradeLevelFee $gradeLevelFee)
    {
        // Check if user has permission to delete fees
        if (! auth()->user()->can('grade_level_fees.delete')) {
            abort(403, 'Unauthorized to delete grade level fees');
        }

        $gradeLevelFee->delete();

        return redirect()->route('registrar.grade-level-fees.index')
            ->with('success', 'Grade level fee deleted successfully.');
    }

    /**
     * Duplicate fees to a new school year.
     */
    public function duplicate(Request $request, GradeLevelFee $gradeLevelFee)
    {
        // Check if user has permission to create fees (duplication creates new fees)
        if (! auth()->user()->can('grade_level_fees.create')) {
            abort(403, 'Unauthorized to create grade level fees');
        }

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

        return redirect()->route('registrar.grade-level-fees.index')
            ->with('success', 'Grade level fee duplicated successfully for school year '.$schoolYear->name);
    }
}
