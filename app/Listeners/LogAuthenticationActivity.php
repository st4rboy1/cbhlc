<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'guard' => $event->guard,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('User logged in');
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'guard' => $event->guard,
                'ip_address' => request()->ip(),
            ])
            ->log('User logged out');
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailed(Failed $event): void
    {
        activity()
            ->withProperties([
                'email' => $event->credentials['email'] ?? 'unknown',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard,
            ])
            ->log('Failed login attempt');
    }
}
