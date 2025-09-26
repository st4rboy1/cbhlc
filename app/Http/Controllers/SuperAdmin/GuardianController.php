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

        $query = Guardian::with(['user', 'children']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $guardians = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('super-admin/guardians/index', [
            'guardians' => $guardians,
            'filters' => $request->only(['search']),
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
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'relationship_type' => $validated['relationship_type'],
                'phone' => $validated['phone'],
                'occupation' => $validated['occupation'],
                'employer' => $validated['employer'],
                'address' => $validated['address'],
                'emergency_contact' => $validated['emergency_contact'] ?? false,
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

        return Inertia::render('super-admin/guardians/show', [
            'guardian' => $guardian,
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

            if ($request->filled('password')) {
                $guardian->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            // Update guardian profile
            $guardian->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'relationship_type' => $validated['relationship_type'],
                'phone' => $validated['phone'],
                'occupation' => $validated['occupation'],
                'employer' => $validated['employer'],
                'address' => $validated['address'],
                'emergency_contact' => $validated['emergency_contact'] ?? false,
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
