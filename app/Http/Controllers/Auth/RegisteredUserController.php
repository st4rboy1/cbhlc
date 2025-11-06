<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        Log::info('Registration attempt started', [
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'has_password' => ! empty($request->password),
            'has_password_confirmation' => ! empty($request->password_confirmation),
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'occupation' => 'nullable|string|max:100',
            'employer' => 'nullable|string|max:255',
        ]);

        Log::info('Registration validation passed', ['email' => $request->email]);

        try {
            DB::beginTransaction();

            Log::info('Creating user', ['email' => $request->email]);

            // Create full name from first and last name
            $fullName = trim($request->first_name.' '.$request->last_name);

            $user = User::create([
                'name' => $fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Registration is limited to guardians only
            $user->assignRole('guardian');

            // Create Guardian profile with complete information from registration
            Guardian::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'occupation' => $request->occupation,
                'employer' => $request->employer,
            ]);

            DB::commit();

            Log::info('User and guardian created successfully', ['email' => $request->email]);

            // Dispatch Registered event - Laravel will automatically send email verification
            // if User implements MustVerifyEmail interface
            event(new Registered($user));

            // Log in the user but they will be redirected to verification notice
            Auth::login($user);

            // Redirect to email verification notice page
            return redirect()->route('verification.notice')
                ->with('status', 'registration-success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Guardian registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'email' => 'Registration failed. Please try again or contact support if the problem persists.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }
}
