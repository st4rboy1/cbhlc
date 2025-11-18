<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $query = User::with('roles');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', User::class);

        $roles = Role::all();

        return Inertia::render('admin/users/create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);

        $user->load('roles', 'permissions');

        // Load guardian profile if user has guardian role
        if ($user->hasRole('guardian')) {
            $user->load('guardian');
        }

        return Inertia::render('admin/users/show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $user->load('roles');

        // Load guardian profile if user has guardian role
        if ($user->hasRole('guardian')) {
            $user->load('guardian');
        }

        $roles = Role::all();

        return Inertia::render('admin/users/edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'address' => $validated['address'] ?? $user->address,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
