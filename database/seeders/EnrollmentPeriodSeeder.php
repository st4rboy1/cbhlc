<?php

namespace Database\Seeders;

use App\Models\EnrollmentPeriod;
use Illuminate\Database\Seeder;

class EnrollmentPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create school years
        $sy2025 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['start_year' => 2025, 'end_year' => 2026, 'start_date' => '2025-06-01', 'end_date' => '2026-05-31', 'status' => 'active']
        );
        $sy2026 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2026-2027'],
            ['start_year' => 2026, 'end_year' => 2027, 'start_date' => '2026-06-01', 'end_date' => '2027-05-31', 'status' => 'upcoming']
        );
        $sy2024 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2024-2025'],
            ['start_year' => 2024, 'end_year' => 2025, 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'status' => 'completed']
        );
        $sy2023 = \App\Models\SchoolYear::firstOrCreate(
            ['name' => '2023-2024'],
            ['start_year' => 2023, 'end_year' => 2024, 'start_date' => '2023-06-01', 'end_date' => '2024-05-31', 'status' => 'completed']
        );

        // Current/Active Period: 2025-2026
        EnrollmentPeriod::create([
            'school_year_id' => $sy2025->id,
            'start_date' => '2025-06-01',
            'end_date' => '2026-03-31',
            'early_registration_deadline' => '2025-05-31',
            'regular_registration_deadline' => '2025-07-31',
            'late_registration_deadline' => '2025-08-31',
            'status' => 'active',
            'description' => 'School Year 2025-2026 Enrollment Period',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        // Upcoming Period: 2026-2027
        EnrollmentPeriod::create([
            'school_year_id' => $sy2026->id,
            'start_date' => '2026-06-01',
            'end_date' => '2027-03-31',
            'early_registration_deadline' => '2026-05-31',
            'regular_registration_deadline' => '2026-07-31',
            'late_registration_deadline' => '2026-08-31',
            'status' => 'upcoming',
            'description' => 'School Year 2026-2027 Enrollment Period',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        // Past Period: 2024-2025
        EnrollmentPeriod::create([
            'school_year_id' => $sy2024->id,
            'start_date' => '2024-06-01',
            'end_date' => '2025-03-31',
            'early_registration_deadline' => '2024-05-31',
            'regular_registration_deadline' => '2024-07-31',
            'late_registration_deadline' => '2024-08-31',
            'status' => 'completed',
            'description' => 'School Year 2024-2025 Enrollment Period',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);

        // Past Period: 2023-2024
        EnrollmentPeriod::create([
            'school_year_id' => $sy2023->id,
            'start_date' => '2023-06-01',
            'end_date' => '2024-03-31',
            'early_registration_deadline' => '2023-05-31',
            'regular_registration_deadline' => '2023-07-31',
            'late_registration_deadline' => '2023-08-31',
            'status' => 'completed',
            'description' => 'School Year 2023-2024 Enrollment Period',
            'allow_new_students' => true,
            'allow_returning_students' => true,
        ]);
    }
}
