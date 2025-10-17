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

        return Inertia::render('super-admin/grade-level-fees/index', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'school_year', 'active']),
            'gradeLevels' => \App\Enums\GradeLevel::values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', GradeLevelFee::class);

        return Inertia::render('super-admin/grade-level-fees/create', [
            'gradeLevels' => \App\Enums\GradeLevel::values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGradeLevelFeeRequest $request)
    {
        Gate::authorize('create', GradeLevelFee::class);

        GradeLevelFee::create($request->validated());

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

        return Inertia::render('super-admin/grade-level-fees/edit', [
            'fee' => $gradeLevelFee,
            'gradeLevels' => \App\Enums\GradeLevel::values(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGradeLevelFeeRequest $request, GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('update', $gradeLevelFee);

        $gradeLevelFee->update($request->validated());

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
}
