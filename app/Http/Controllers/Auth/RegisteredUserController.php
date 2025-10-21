<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'occupation' => 'required|string|max:100',
            'employer' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Registration is limited to guardians only
        $user->assignRole('guardian');

        // Create Guardian profile with complete information from registration
        // Split the name into first and last name (simple approach)
        $nameParts = explode(' ', $request->name, 2);
        Guardian::create([
            'user_id' => $user->id,
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'occupation' => $request->occupation,
            'employer' => $request->employer,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect to guardian dashboard
        return redirect()->route('guardian.dashboard');
    }
}
