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
        try {
            // Get all registrars
            $registrars = User::role('registrar')->get();

            if ($registrars->isEmpty()) {
                // No registrars to notify, log and return
                \Log::info('No registrars found to notify about new student', [
                    'student_id' => $event->student->id,
                ]);

                return;
            }

            // Notify each registrar (queued to prevent blocking)
            foreach ($registrars as $registrar) {
                $registrar->notify((new NewStudentCreatedNotification($event->student))->delay(now()->addSeconds(10)));
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the student creation
            \Log::error('Failed to notify registrars about new student', [
                'student_id' => $event->student->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
