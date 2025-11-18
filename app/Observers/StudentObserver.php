<?php

namespace App\Observers;

use App\Models\Student;

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
        // Note: Activity logging is handled automatically by LogsActivity trait
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        // Note: Activity logging is handled automatically by LogsActivity trait
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        // Note: Activity logging is handled automatically by LogsActivity trait
    }

    /**
     * Generate a unique student ID.
     */
    private function generateStudentId(): string
    {
        $year = now()->format('Y');

        // Get the latest student ID for this year
        $latestStudent = Student::where('student_id', 'like', $year.'%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($latestStudent) {
            // Extract the sequence number and increment
            $sequence = intval(substr($latestStudent->student_id, 5)) + 1;
        } else {
            // Start with 1 if no students for this year
            $sequence = 1;
        }

        return sprintf('%d-%04d', $year, $sequence);
    }
}
