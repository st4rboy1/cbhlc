<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Seed default users
        $this->call(UserSeeder::class);

        // Seed settings
        $this->call(SettingsSeeder::class);

        // Seed school information
        $this->call(SchoolInformationSeeder::class);

        // Seed enrollment periods
        $this->call(EnrollmentPeriodSeeder::class);

        // Seed grade level fees
        $this->call(GradeLevelFeeSeeder::class);

        // Seed enrollments (includes students and guardians if needed)
        $this->call(EnrollmentSeeder::class);

        // Seed invoices for enrollments
        $this->call(InvoiceSeeder::class);

        // Seed payments for enrollments
        $this->call(PaymentSeeder::class);
    }
}
