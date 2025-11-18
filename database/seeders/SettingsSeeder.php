<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Core system settings
        Setting::updateOrCreate(
            ['key' => 'school_name'],
            [
                'value' => 'Christian Bible Heritage Learning Center',
                'type' => 'string',
                'description' => 'Official school name displayed throughout the system',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'timezone'],
            [
                'value' => 'Asia/Manila',
                'type' => 'string',
                'description' => 'Default timezone for the system',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'items_per_page'],
            [
                'value' => '10',
                'type' => 'integer',
                'description' => 'Number of items to display per page in listings',
                'is_public' => false,
            ]
        );

        // School contact information
        Setting::updateOrCreate(
            ['key' => 'school_address'],
            [
                'value' => '123 School St, City, Country',
                'type' => 'string',
                'description' => 'School physical address',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'school_phone'],
            [
                'value' => '(02) 123-4567',
                'type' => 'string',
                'description' => 'School contact phone number',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'school_email'],
            [
                'value' => 'info@cbhlc.edu',
                'type' => 'string',
                'description' => 'School contact email address',
                'is_public' => true,
            ]
        );

        // Payment settings
        Setting::updateOrCreate(
            ['key' => 'payment_location'],
            [
                'value' => 'Visit the school cashier\'s office during business hours.',
                'type' => 'string',
                'description' => 'Payment location information',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'payment_hours'],
            [
                'value' => 'Monday to Friday, 8:00 AM - 5:00 PM',
                'type' => 'string',
                'description' => 'Payment office hours',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'payment_methods'],
            [
                'value' => 'Cash (Face-to-face payment only)',
                'type' => 'string',
                'description' => 'Accepted payment methods',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'payment_note'],
            [
                'value' => 'Please bring this tuition statement and a valid ID when making payment.',
                'type' => 'string',
                'description' => 'Additional payment instructions',
                'is_public' => true,
            ]
        );
    }
}
