<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SchoolInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolInfo = [
            // Contact Information
            [
                'key' => 'school_name',
                'value' => 'Christian Bible Heritage Learning Center',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'School Name',
                'description' => 'Official name of the school',
                'order' => 1,
            ],
            [
                'key' => 'school_email',
                'value' => 'info@cbhlc.edu.ph',
                'type' => 'email',
                'group' => 'contact',
                'label' => 'Email Address',
                'description' => 'Primary contact email',
                'order' => 2,
            ],
            [
                'key' => 'school_phone',
                'value' => '(02) 1234-5678',
                'type' => 'phone',
                'group' => 'contact',
                'label' => 'Phone Number',
                'description' => 'Primary contact phone number',
                'order' => 3,
            ],
            [
                'key' => 'school_mobile',
                'value' => '+63 917 123 4567',
                'type' => 'phone',
                'group' => 'contact',
                'label' => 'Mobile Number',
                'description' => 'Mobile contact number',
                'order' => 4,
            ],
            [
                'key' => 'school_address',
                'value' => '123 Education St., Manila, Philippines',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'School Address',
                'description' => 'Physical address of the school',
                'order' => 5,
            ],

            // Office Hours
            [
                'key' => 'office_hours_weekday',
                'value' => 'Monday to Friday: 8:00 AM - 5:00 PM',
                'type' => 'text',
                'group' => 'hours',
                'label' => 'Weekday Hours',
                'description' => 'Office hours during weekdays',
                'order' => 1,
            ],
            [
                'key' => 'office_hours_saturday',
                'value' => 'Saturday: 8:00 AM - 12:00 PM',
                'type' => 'text',
                'group' => 'hours',
                'label' => 'Saturday Hours',
                'description' => 'Office hours on Saturday',
                'order' => 2,
            ],
            [
                'key' => 'office_hours_sunday',
                'value' => 'Sunday: Closed',
                'type' => 'text',
                'group' => 'hours',
                'label' => 'Sunday Hours',
                'description' => 'Office hours on Sunday',
                'order' => 3,
            ],

            // Social Media
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/cbhlc',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Facebook URL',
                'description' => 'School Facebook page',
                'order' => 1,
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/cbhlc',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Instagram URL',
                'description' => 'School Instagram account',
                'order' => 2,
            ],
            [
                'key' => 'youtube_url',
                'value' => 'https://youtube.com/@cbhlc',
                'type' => 'url',
                'group' => 'social',
                'label' => 'YouTube URL',
                'description' => 'School YouTube channel',
                'order' => 3,
            ],

            // About
            [
                'key' => 'school_tagline',
                'value' => 'Nurturing Young Minds with Christian Values',
                'type' => 'text',
                'group' => 'about',
                'label' => 'School Tagline',
                'description' => 'School motto or tagline',
                'order' => 1,
            ],
            [
                'key' => 'school_description',
                'value' => 'Christian Bible Heritage Learning Center is committed to providing quality education grounded in Christian values.',
                'type' => 'text',
                'group' => 'about',
                'label' => 'School Description',
                'description' => 'Brief description of the school',
                'order' => 2,
            ],
        ];

        foreach ($schoolInfo as $info) {
            DB::table('school_information')->insert(array_merge($info, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Clear the cache to ensure fresh data is loaded
        Cache::forget('school_information');
    }
}
