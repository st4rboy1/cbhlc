<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserRegisteredNotification;
use Illuminate\Auth\Events\Registered;

class NotifySuperAdminOfNewUser
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
        // Get all super admins
        $superAdmins = User::role('super_admin')->get();

        // Get the registered user
        /** @var User $registeredUser */
        $registeredUser = $event->user;

        // Notify each super admin
        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify(new NewUserRegisteredNotification($registeredUser));
        }
    }
}
