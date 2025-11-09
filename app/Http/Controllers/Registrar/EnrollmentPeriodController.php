<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentPeriod;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EnrollmentPeriodController extends Controller
{
    /**
     * Display a listing of enrollment periods.
     */
    public function index()
    {
        Gate::authorize('viewAny', EnrollmentPeriod::class);

        $periods = EnrollmentPeriod::with('schoolYear')
            ->latest('start_date')
            ->withCount('enrollments')
            ->paginate(10);

        $activePeriod = EnrollmentPeriod::with('schoolYear')
            ->withCount('enrollments')
            ->active()
            ->first();

        return Inertia::render('registrar/enrollment-periods/index', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }

    /**
     * Display the specified enrollment period.
     */
    public function show(EnrollmentPeriod $enrollmentPeriod)
    {
        Gate::authorize('view', $enrollmentPeriod);

        $enrollmentPeriod->loadCount('enrollments');

        return Inertia::render('registrar/enrollment-periods/show', [
            'period' => $enrollmentPeriod,
        ]);
    }

    // Note: create, store, edit, update, destroy, activate, and close methods removed - registrar only has view permission
}
