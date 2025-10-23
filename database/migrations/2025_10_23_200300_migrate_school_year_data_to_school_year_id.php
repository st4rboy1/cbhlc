<?php

use App\Models\Enrollment;
use App\Models\SchoolYear;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all unique school_year values from enrollments
        $schoolYearStrings = Enrollment::whereNotNull('school_year')
            ->distinct()
            ->pluck('school_year');

        foreach ($schoolYearStrings as $schoolYearString) {
            // Find or create the school year record
            $schoolYear = SchoolYear::firstOrCreate(
                ['name' => $schoolYearString],
                [
                    'start_year' => (int) explode('-', $schoolYearString)[0],
                    'end_year' => (int) explode('-', $schoolYearString)[1],
                    'start_date' => explode('-', $schoolYearString)[0].'-06-01',
                    'end_date' => explode('-', $schoolYearString)[1].'-05-31',
                    'status' => 'completed',
                    'is_active' => false,
                ]
            );

            // Update all enrollments with this school_year string to use the ID
            Enrollment::where('school_year', $schoolYearString)
                ->update(['school_year_id' => $schoolYear->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore school_year strings from school_year_id
        $enrollments = Enrollment::whereNotNull('school_year_id')->with('schoolYear')->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->schoolYear) {
                $enrollment->update(['school_year' => $enrollment->schoolYear->name]);
            }
        }

        // Clear school_year_id
        Enrollment::whereNotNull('school_year_id')->update(['school_year_id' => null]);
    }
};
