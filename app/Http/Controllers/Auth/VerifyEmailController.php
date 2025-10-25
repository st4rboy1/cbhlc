<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserEmailVerified;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route($request->user()->getDashboardRoute(), absolute: false).'?verified=1');
        }

        $request->fulfill();

        // Dispatch event to notify admins
        event(new UserEmailVerified(
            $request->user(),
            $request->ip() ?? '127.0.0.1',
            $request->userAgent() ?? 'Unknown'
        ));

        return redirect()->intended(route($request->user()->getDashboardRoute(), absolute: false).'?verified=1');
    }
}
