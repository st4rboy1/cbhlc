<?php

namespace Database\Seeders;

use App\Models\GradeLevelFee;
use Illuminate\Database\Seeder;

class GradeLevelFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYear = '2025-2026';

        // KINDER - ANNUAL (Nursery and Kinder have same fees per CSV)
        GradeLevelFee::create([
            'grade_level' => 'Kinder',
            'school_year' => $schoolYear,
            'tuition_fee_cents' => 1850000, // ₱18,500.00
            'miscellaneous_fee_cents' => 550000, // ₱5,500.00
            'other_fees_cents' => 0,
            'down_payment_cents' => 0, // Full payment upfront
            'payment_terms' => 'ANNUAL',
            'is_active' => true,
        ]);

        // KINDER - SEMESTRAL
        GradeLevelFee::create([
            'grade_level' => 'Kinder',
            'school_year' => $schoolYear,
            'tuition_fee_cents' => 1900000, // ₱9,500 x 2 = ₱19,000 (tuition only)
            'miscellaneous_fee_cents' => 550000, // ₱5,500.00
            'other_fees_cents' => 0,
            'down_payment_cents' => 1500000, // ₱15,000.00 on enrollment
            'payment_terms' => 'SEMESTRAL',
            'is_active' => true,
        ]);

        // KINDER - MONTHLY
        GradeLevelFee::create([
            'grade_level' => 'Kinder',
            'school_year' => $schoolYear,
            'tuition_fee_cents' => 1950000, // ₱1,950 x 10 = ₱19,500 (tuition only)
            'miscellaneous_fee_cents' => 550000, // ₱5,500.00
            'other_fees_cents' => 0,
            'down_payment_cents' => 745000, // ₱7,450.00 on enrollment
            'payment_terms' => 'MONTHLY',
            'is_active' => true,
        ]);

        // GRADES 1, 2, 3 - ANNUAL
        foreach (['Grade 1', 'Grade 2', 'Grade 3'] as $gradeLevel) {
            GradeLevelFee::create([
                'grade_level' => $gradeLevel,
                'school_year' => $schoolYear,
                'tuition_fee_cents' => 2050000, // ₱20,500.00
                'miscellaneous_fee_cents' => 650000, // ₱6,500.00
                'other_fees_cents' => 0,
                'down_payment_cents' => 0, // Full payment upfront
                'payment_terms' => 'ANNUAL',
                'is_active' => true,
            ]);
        }

        // GRADES 1, 2, 3 - SEMESTRAL
        foreach (['Grade 1', 'Grade 2', 'Grade 3'] as $gradeLevel) {
            GradeLevelFee::create([
                'grade_level' => $gradeLevel,
                'school_year' => $schoolYear,
                'tuition_fee_cents' => 2100000, // ₱10,500 x 2 = ₱21,000 (tuition only)
                'miscellaneous_fee_cents' => 650000, // ₱6,500.00
                'other_fees_cents' => 0,
                'down_payment_cents' => 1700000, // ₱17,000.00 on enrollment
                'payment_terms' => 'SEMESTRAL',
                'is_active' => true,
            ]);
        }

        // GRADES 1, 2, 3 - MONTHLY
        foreach (['Grade 1', 'Grade 2', 'Grade 3'] as $gradeLevel) {
            GradeLevelFee::create([
                'grade_level' => $gradeLevel,
                'school_year' => $schoolYear,
                'tuition_fee_cents' => 2150000, // ₱2,150 x 10 = ₱21,500 (tuition only)
                'miscellaneous_fee_cents' => 650000, // ₱6,500.00
                'other_fees_cents' => 0,
                'down_payment_cents' => 865000, // ₱8,650.00 on enrollment (₱2,150 + ₱6,500)
                'payment_terms' => 'MONTHLY',
                'is_active' => true,
            ]);
        }
    }
}
