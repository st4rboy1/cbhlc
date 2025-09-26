<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
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
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
        }

        $query = GradeLevelFee::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('grade_level', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
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

        return Inertia::render('registrar/grade-level-fees/index', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'school_year', 'active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
        }

        return Inertia::render('registrar/grade-level-fees/create', [
            'gradelevels' => GradeLevel::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
        }

        $validated = $request->validate([
            'grade_level' => ['required', 'string'],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'enrollment_fee' => ['required', 'numeric', 'min:0'],
            'tuition_fee' => ['required', 'numeric', 'min:0'],
            'miscellaneous_fee' => ['required', 'numeric', 'min:0'],
            'computer_fee' => ['nullable', 'numeric', 'min:0'],
            'library_fee' => ['nullable', 'numeric', 'min:0'],
            'laboratory_fee' => ['nullable', 'numeric', 'min:0'],
            'pe_uniform_fee' => ['nullable', 'numeric', 'min:0'],
            'school_uniform_fee' => ['nullable', 'numeric', 'min:0'],
            'books_fee' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

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
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
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
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
        }

        return Inertia::render('registrar/grade-level-fees/edit', [
            'fee' => $gradeLevelFee,
            'gradelevels' => GradeLevel::cases(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GradeLevelFee $gradeLevelFee)
    {
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
        }

        $validated = $request->validate([
            'grade_level' => ['required', 'string'],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'enrollment_fee' => ['required', 'numeric', 'min:0'],
            'tuition_fee' => ['required', 'numeric', 'min:0'],
            'miscellaneous_fee' => ['required', 'numeric', 'min:0'],
            'computer_fee' => ['nullable', 'numeric', 'min:0'],
            'library_fee' => ['nullable', 'numeric', 'min:0'],
            'laboratory_fee' => ['nullable', 'numeric', 'min:0'],
            'pe_uniform_fee' => ['nullable', 'numeric', 'min:0'],
            'school_uniform_fee' => ['nullable', 'numeric', 'min:0'],
            'books_fee' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

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
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
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
        // Check if user has permission to manage fees
        if (! auth()->user()->can('grade_level_fees.manage')) {
            abort(403, 'Unauthorized to manage grade level fees');
        }

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
        $newFee->created_by = auth()->id();
        $newFee->save();

        return redirect()->route('registrar.grade-level-fees.index')
            ->with('success', 'Grade level fee duplicated successfully for school year '.$validated['school_year']);
    }
}