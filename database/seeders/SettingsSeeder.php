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
        Setting::updateOrCreate(['key' => 'payment_location'], ['value' => 'Visit the school cashier\'s office during business hours.']);
        Setting::updateOrCreate(['key' => 'payment_hours'], ['value' => 'Monday to Friday, 8:00 AM - 5:00 PM']);
        Setting::updateOrCreate(['key' => 'payment_methods'], ['value' => 'Cash or Check (Face-to-face payment only)']);
        Setting::updateOrCreate(['key' => 'payment_note'], ['value' => 'Please bring this tuition statement and a valid ID when making payment.']);

        Setting::updateOrCreate(['key' => 'school_name'], ['value' => 'Christian Bible Heritage Learning Center']);
        Setting::updateOrCreate(['key' => 'school_address'], ['value' => '123 School St, City, Country']);
        Setting::updateOrCreate(['key' => 'school_phone'], ['value' => '(02) 123-4567']);
        Setting::updateOrCreate(['key' => 'school_email'], ['value' => 'info@cbhlc.edu']);
    }
}
