<?php

namespace App\Listeners;

use App\Events\StudentCreated;
use App\Models\User;
use App\Notifications\NewStudentCreatedNotification;

class NotifyRegistrarOfNewStudent
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
    public function handle(StudentCreated $event): void
    {
        // Get all registrars
        $registrars = User::role('registrar')->get();

        // Notify each registrar
        foreach ($registrars as $registrar) {
            $registrar->notify(new NewStudentCreatedNotification($event->student));
        }
    }
}
