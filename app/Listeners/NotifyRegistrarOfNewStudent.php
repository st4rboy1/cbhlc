<?php

namespace App\Listeners;

use App\Events\StudentCreated;
use App\Models\User;
use App\Notifications\NewStudentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyRegistrarOfNewStudent implements ShouldQueue
{
    use InteractsWithQueue;

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
