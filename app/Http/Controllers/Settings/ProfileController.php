<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $guardian = $user->hasRole('guardian') ? $user->guardian : null;

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'guardian' => $guardian,
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update user account info
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update guardian profile if user is a guardian
        if ($user->hasRole('guardian') && $user->guardian) {
            $user->guardian->update([
                'first_name' => $validated['first_name'] ?? $user->guardian->first_name,
                'middle_name' => $validated['middle_name'] ?? $user->guardian->middle_name,
                'last_name' => $validated['last_name'] ?? $user->guardian->last_name,
                'contact_number' => $validated['contact_number'] ?? $user->guardian->contact_number,
                'address' => $validated['address'] ?? $user->guardian->address,
                'occupation' => $validated['occupation'] ?? $user->guardian->occupation,
                'employer' => $validated['employer'] ?? $user->guardian->employer,
            ]);
        }

        return to_route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
