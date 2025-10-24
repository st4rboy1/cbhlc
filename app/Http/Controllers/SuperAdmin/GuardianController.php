<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreGuardianRequest;
use App\Http\Requests\SuperAdmin\UpdateGuardianRequest;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class GuardianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Guardian::class);

        $query = Guardian::with(['user', 'children'])->withCount('children as students_count');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $guardians = $query->latest()->paginate(15)->withQueryString();

        // Transform guardian data to match frontend expectations
        /** @phpstan-ignore argument.unresolvableType */
        $guardians->getCollection()->transform(function ($guardian) {
            // Get primary relationship from first child if exists
            $relationship = 'guardian';
            $emergencyContact = false;
            if ($guardian->children->isNotEmpty()) {
                $firstChild = $guardian->children->first();
                $relationship = $firstChild->pivot->relationship_type ?? 'guardian';
                $emergencyContact = $firstChild->pivot->is_primary_contact ?? false;
            }

            return (object) [
                'id' => $guardian->id,
                'first_name' => $guardian->first_name,
                'middle_name' => $guardian->middle_name,
                'last_name' => $guardian->last_name,
                'email' => $guardian->user->email,
                'phone' => $guardian->contact_number,
                'relationship' => $relationship,
                'emergency_contact' => $emergencyContact,
                'students_count' => $guardian->students_count,
                'created_at' => $guardian->created_at,
                'updated_at' => $guardian->updated_at,
            ];
        });

        // Calculate stats
        $stats = [
            'total' => Guardian::count(),
            'with_students' => Guardian::has('children')->count(),
            'without_students' => Guardian::doesntHave('children')->count(),
            'emergency_contacts' => Guardian::whereHas('children', function ($q) {
                $q->where('is_primary_contact', true);
            })->count(),
        ];

        return Inertia::render('super-admin/guardians/index', [
            'guardians' => $guardians,
            'filters' => $request->only(['search']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Guardian::class);

        return Inertia::render('super-admin/guardians/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuardianRequest $request)
    {
        Gate::authorize('create', Guardian::class);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            // Create user account
            $user = User::create([
                'name' => $validated['first_name'].' '.$validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('guardian');

            // Create guardian profile
            Guardian::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'contact_number' => $validated['phone'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'employer' => $validated['employer'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()->route('super-admin.guardians.index')
            ->with('success', 'Guardian created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Guardian $guardian)
    {
        Gate::authorize('view', $guardian);

        $guardian->load(['user', 'children.enrollments']);

        // Get primary relationship from first child if exists
        $relationship = 'guardian';
        $emergencyContact = false;
        if ($guardian->children->isNotEmpty()) {
            $firstChild = $guardian->children->first();
            $relationship = $firstChild->pivot->relationship_type ?? 'guardian';
            $emergencyContact = $firstChild->pivot->is_primary_contact ?? false;
        }

        // Transform guardian data to match frontend expectations
        $guardianData = [
            'id' => $guardian->id,
            'first_name' => $guardian->first_name,
            'middle_name' => $guardian->middle_name,
            'last_name' => $guardian->last_name,
            'email' => $guardian->user->email,
            'phone' => $guardian->contact_number,
            'address' => $guardian->address,
            'relationship' => $relationship,
            'occupation' => $guardian->occupation,
            'employer' => $guardian->employer,
            'emergency_contact' => $emergencyContact,
            'created_at' => $guardian->created_at,
            'updated_at' => $guardian->updated_at,
        ];

        // Transform students data
        /** @phpstan-ignore argument.unresolvableType */
        $students = $guardian->children->map(function ($student) {
            return [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'grade_level' => $student->grade_level,
                'email' => $student->email,
            ];
        });

        // Transform enrollments data
        $enrollments = $guardian->children->flatMap(function ($student) {
            return $student->enrollments->map(function ($enrollment) use ($student) {
                return [
                    'id' => $enrollment->id,
                    'student' => [
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                    ],
                    'school_year' => $enrollment->schoolYear->name,
                    'grade_level' => $enrollment->grade_level,
                    'status' => $enrollment->status,
                    'enrollment_date' => $enrollment->created_at,
                ];
            });
        });

        return Inertia::render('super-admin/guardians/show', [
            'guardian' => $guardianData,
            'students' => $students,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guardian $guardian)
    {
        Gate::authorize('update', $guardian);

        $guardian->load('user');

        return Inertia::render('super-admin/guardians/edit', [
            'guardian' => $guardian,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGuardianRequest $request, Guardian $guardian)
    {
        Gate::authorize('update', $guardian);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $guardian) {
            // Update user account
            $guardian->user->update([
                'name' => $validated['first_name'].' '.$validated['last_name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $guardian->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            // Update guardian profile
            $guardian->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'contact_number' => $validated['phone'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'employer' => $validated['employer'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()->route('super-admin.guardians.index')
            ->with('success', 'Guardian updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guardian $guardian)
    {
        Gate::authorize('delete', $guardian);

        // Check if guardian has children
        if ($guardian->children()->exists()) {
            return redirect()->route('super-admin.guardians.index')
                ->with('error', 'Cannot delete guardian with existing students.');
        }

        DB::transaction(function () use ($guardian) {
            $guardian->user->delete(); // This will also delete the guardian due to cascade
        });

        return redirect()->route('super-admin.guardians.index')
            ->with('success', 'Guardian deleted successfully.');
    }
}
