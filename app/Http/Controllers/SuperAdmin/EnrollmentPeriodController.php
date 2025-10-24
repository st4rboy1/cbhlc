<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentPeriodRequest;
use App\Http\Requests\UpdateEnrollmentPeriodRequest;
use App\Models\EnrollmentPeriod;
use Inertia\Inertia;

class EnrollmentPeriodController extends Controller
{
    /**
     * Display a listing of enrollment periods.
     */
    public function index()
    {
        $periods = EnrollmentPeriod::with('schoolYear')
            ->latest('start_date')
            ->withCount('enrollments')
            ->paginate(10);

        $activePeriod = EnrollmentPeriod::with('schoolYear')
            ->withCount('enrollments')
            ->active()
            ->first();

        return Inertia::render('super-admin/enrollment-periods/index', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }

    /**
     * Show the form for creating a new enrollment period.
     */
    public function create()
    {
        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('super-admin/enrollment-periods/create', [
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Store a newly created enrollment period.
     */
    public function store(StoreEnrollmentPeriodRequest $request)
    {
        $validated = $request->validated();

        $period = EnrollmentPeriod::create($validated);

        activity()
            ->performedOn($period)
            ->withProperties($period->toArray())
            ->log('Enrollment period created');

        return redirect()
            ->route('super-admin.enrollment-periods.index')
            ->with('success', 'Enrollment period created successfully.');
    }

    /**
     * Display the specified enrollment period.
     */
    public function show(EnrollmentPeriod $enrollmentPeriod)
    {
        $enrollmentPeriod->loadCount('enrollments');

        return Inertia::render('super-admin/enrollment-periods/show', [
            'period' => $enrollmentPeriod,
        ]);
    }

    /**
     * Show the form for editing the specified enrollment period.
     */
    public function edit(EnrollmentPeriod $enrollmentPeriod)
    {
        // Get available school years (active and upcoming)
        $schoolYears = \App\Models\SchoolYear::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_year', 'desc')
            ->get();

        return Inertia::render('super-admin/enrollment-periods/edit', [
            'period' => $enrollmentPeriod,
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Update the specified enrollment period.
     */
    public function update(UpdateEnrollmentPeriodRequest $request, EnrollmentPeriod $enrollmentPeriod)
    {
        $old = $enrollmentPeriod->toArray();

        $validated = $request->validated();

        $enrollmentPeriod->update($validated);

        activity()
            ->performedOn($enrollmentPeriod)
            ->withProperties([
                'old' => $old,
                'new' => $enrollmentPeriod->toArray(),
            ])
            ->log('Enrollment period updated');

        return redirect()
            ->route('super-admin.enrollment-periods.show', $enrollmentPeriod)
            ->with('success', 'Enrollment period updated successfully.');
    }

    /**
     * Remove the specified enrollment period.
     */
    public function destroy(EnrollmentPeriod $enrollmentPeriod)
    {
        // Prevent deletion of active period
        if ($enrollmentPeriod->isActive()) {
            return back()->withErrors([
                'period' => 'Cannot delete an active enrollment period.',
            ]);
        }

        // Prevent deletion if enrollments exist
        if ($enrollmentPeriod->enrollments()->exists()) {
            return back()->withErrors([
                'period' => 'Cannot delete period with existing enrollments.',
            ]);
        }

        activity()
            ->performedOn($enrollmentPeriod)
            ->withProperties($enrollmentPeriod->toArray())
            ->log('Enrollment period deleted');

        $enrollmentPeriod->delete();

        return redirect()
            ->route('super-admin.enrollment-periods.index')
            ->with('success', 'Enrollment period deleted successfully.');
    }

    /**
     * Activate the specified enrollment period.
     */
    public function activate(EnrollmentPeriod $enrollmentPeriod)
    {
        // Close other active periods
        EnrollmentPeriod::where('status', 'active')->update(['status' => 'closed']);

        $enrollmentPeriod->update(['status' => 'active']);

        activity()
            ->performedOn($enrollmentPeriod)
            ->withProperties([
                'previous_status' => 'upcoming',
                'new_status' => 'active',
            ])
            ->log('Enrollment period activated');

        return back()->with('success', 'Enrollment period activated successfully.');
    }

    /**
     * Close the specified enrollment period.
     */
    public function close(EnrollmentPeriod $enrollmentPeriod)
    {
        if (! $enrollmentPeriod->isActive()) {
            return back()->withErrors(['period' => 'Only active periods can be closed.']);
        }

        $enrollmentPeriod->update(['status' => 'closed']);

        activity()
            ->performedOn($enrollmentPeriod)
            ->withProperties([
                'previous_status' => 'active',
                'new_status' => 'closed',
            ])
            ->log('Enrollment period closed');

        return back()->with('success', 'Enrollment period closed successfully.');
    }
}
