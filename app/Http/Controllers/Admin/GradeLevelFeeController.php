<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeLevelFeeRequest;
use App\Http\Requests\UpdateGradeLevelFeeRequest;
use App\Models\GradeLevelFee;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GradeLevelFeeController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/grade-level-fees/index');
    }

    public function create()
    {
        Gate::authorize('create', GradeLevelFee::class);

        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('admin/grade-level-fees/create', [
            'gradeLevels' => \App\Enums\GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    public function store(StoreGradeLevelFeeRequest $request)
    {
        Gate::authorize('create', GradeLevelFee::class);

        $validated = $request->validated();

        $gradeLevelFee = GradeLevelFee::create($validated);

        return redirect()->route('admin.grade-level-fees.index')
            ->with('success', 'Grade level fee created successfully.');
    }

    public function show(GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('view', $gradeLevelFee);

        return Inertia::render('admin/grade-level-fees/show', [
            'fee' => $gradeLevelFee,
        ]);
    }

    public function edit(GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('update', $gradeLevelFee);

        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('admin/grade-level-fees/edit', [
            'fee' => $gradeLevelFee,
            'gradeLevels' => \App\Enums\GradeLevel::values(),
            'schoolYears' => $schoolYears,
        ]);
    }

    public function update(UpdateGradeLevelFeeRequest $request, GradeLevelFee $gradeLevelFee)
    {
        Gate::authorize('update', $gradeLevelFee);

        $validated = $request->validated();

        $gradeLevelFee->update($validated);

        return redirect()->route('admin.grade-level-fees.index')
            ->with('success', 'Grade level fee updated successfully.');
    }
}
