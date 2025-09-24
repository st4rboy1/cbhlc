<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Support\Str;

class StudentObserver
{
    /**
     * Handle the Student "creating" event.
     */
    public function creating(Student $student): void
    {
        // Generate student ID if not provided
        if (empty($student->student_id)) {
            $student->student_id = $this->generateStudentId();
        }

        // Ensure student ID is uppercase
        $student->student_id = strtoupper($student->student_id);
    }

    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        // Log student creation
        activity()
            ->performedOn($student)
            ->causedBy(auth()->user())
            ->log('Student created: ' . $student->full_name);
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        // Log significant changes
        if ($student->wasChanged(['first_name', 'last_name', 'grade_level'])) {
            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties(['changes' => $student->getChanges()])
                ->log('Student updated: ' . $student->full_name);
        }
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        // Log student deletion
        activity()
            ->performedOn($student)
            ->causedBy(auth()->user())
            ->log('Student deleted: ' . $student->full_name);
    }

    /**
     * Generate a unique student ID.
     */
    private function generateStudentId(): string
    {
        $year = now()->format('Y');

        // Get the latest student ID for this year
        $latestStudent = Student::where('student_id', 'like', $year . '%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($latestStudent) {
            // Extract the sequence number and increment
            $sequence = intval(substr($latestStudent->student_id, 4)) + 1;
        } else {
            // Start with 1 if no students for this year
            $sequence = 1;
        }

        return sprintf('%s%04d', $year, $sequence);
    }
}