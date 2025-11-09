<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
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

        $query = SchoolYear::query()->withCount(['enrollments']);

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

        return Inertia::render('registrar/school-years/index', [
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolYear $schoolYear)
    {
        Gate::authorize('view', $schoolYear);

        $schoolYear->loadCount(['enrollments']);
        $schoolYear->load(['enrollments' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('registrar/school-years/show', [
            'schoolYear' => $schoolYear,
        ]);
    }

    // Note: create, store, edit, update, destroy, and setActive methods removed - registrar only has view permission
}
