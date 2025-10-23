<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        // Determine the current school year based on the month
        // School year typically starts in June/July
        if ($currentMonth >= 6) {
            $activeStartYear = $currentYear;
            $activeEndYear = $currentYear + 1;
        } else {
            $activeStartYear = $currentYear - 1;
            $activeEndYear = $currentYear;
        }

        // Create past school years (last 3 years)
        for ($i = 3; $i > 0; $i--) {
            $startYear = $activeStartYear - $i;
            $endYear = $activeEndYear - $i;

            SchoolYear::create([
                'name' => "{$startYear}-{$endYear}",
                'start_year' => $startYear,
                'end_year' => $endYear,
                'start_date' => "{$startYear}-06-01",
                'end_date' => "{$endYear}-05-31",
                'status' => 'completed',
                'is_active' => false,
            ]);
        }

        // Create current/active school year
        SchoolYear::create([
            'name' => "{$activeStartYear}-{$activeEndYear}",
            'start_year' => $activeStartYear,
            'end_year' => $activeEndYear,
            'start_date' => "{$activeStartYear}-06-01",
            'end_date' => "{$activeEndYear}-05-31",
            'status' => 'active',
            'is_active' => true,
        ]);

        // Create next school year (upcoming)
        $nextStartYear = $activeStartYear + 1;
        $nextEndYear = $activeEndYear + 1;

        SchoolYear::create([
            'name' => "{$nextStartYear}-{$nextEndYear}",
            'start_year' => $nextStartYear,
            'end_year' => $nextEndYear,
            'start_date' => "{$nextStartYear}-06-01",
            'end_date' => "{$nextEndYear}-05-31",
            'status' => 'upcoming',
            'is_active' => false,
        ]);

        $this->command->info('School years seeded successfully!');
        $this->command->info("Active school year: {$activeStartYear}-{$activeEndYear}");
    }
}
