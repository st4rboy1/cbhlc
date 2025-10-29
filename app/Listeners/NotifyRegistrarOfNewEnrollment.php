<?php

namespace App\Listeners;

use App\Events\EnrollmentCreated;
use App\Models\User;
use App\Notifications\NewEnrollmentCreatedNotification;

class NotifyRegistrarOfNewEnrollment
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
    public function handle(EnrollmentCreated $event): void
    {
        // Get all registrars
        $registrars = User::role('registrar')->get();

        // Notify each registrar
        foreach ($registrars as $registrar) {
            $registrar->notify(new NewEnrollmentCreatedNotification($event->enrollment));
        }
    }
}
