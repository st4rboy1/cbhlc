<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserRegisteredNotification;
use Illuminate\Auth\Events\Registered;

class NotifyAdminsOfNewUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Get all super admins and administrators
        $admins = User::role(['super_admin', 'administrator'])->get();

        // Get the registered user
        /** @var User $registeredUser */
        $registeredUser = $event->user;

        // Notify each admin
        foreach ($admins as $admin) {
            $admin->notify(new NewUserRegisteredNotification($registeredUser));
        }
    }
}
