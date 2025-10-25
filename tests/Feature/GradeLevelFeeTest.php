<?php

use App\Enums\GradeLevel;
use App\Models\GradeLevelFee;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('grade level fee model stores money values in cents and returns float', function () {
    $fee = new GradeLevelFee;

    // Test tuition fee attribute
    $fee->tuition_fee = 15000.50;
    expect($fee->tuition_fee_cents)->toBe(1500050);
    expect($fee->tuition_fee)->toBe(15000.50);

    // Test miscellaneous fee attribute
    $fee->miscellaneous_fee = 1234.25;
    expect($fee->miscellaneous_fee_cents)->toBe(123425);
    expect($fee->miscellaneous_fee)->toBe(1234.25);

    // Test laboratory fee attribute
    $fee->laboratory_fee = 500.00;
    expect($fee->laboratory_fee_cents)->toBe(50000);
    expect($fee->laboratory_fee)->toBe(500.00);

    // Test library fee attribute
    $fee->library_fee = 100.75;
    expect($fee->library_fee_cents)->toBe(10075);
    expect($fee->library_fee)->toBe(100.75);

    // Test sports fee attribute
    $fee->sports_fee = 250.30;
    expect($fee->sports_fee_cents)->toBe(25030);
    expect($fee->sports_fee)->toBe(250.30);
});

test('grade level fee model calculates total fee correctly', function () {
    $fee = new GradeLevelFee([
        'tuition_fee_cents' => 1500000, // 15000.00
        'miscellaneous_fee_cents' => 50000, // 500.00
        'laboratory_fee_cents' => 25000, // 250.00
        'library_fee_cents' => 10000, // 100.00
        'sports_fee_cents' => 15000, // 150.00
    ]);

    expect($fee->total_fee)->toBe(16000.00);
});

test('grade level fee model enum casting works correctly', function () {
    $fee = new GradeLevelFee(['grade_level' => 'Kinder']);

    expect($fee->grade_level)->toBeInstanceOf(GradeLevel::class);
    expect($fee->grade_level)->toBe(GradeLevel::KINDER);
});

test('grade level fee model formatted attributes work correctly', function () {
    config([
        'currency.default' => [
            'symbol' => '₱',
            'symbol_position' => 'before',
            'code' => 'PHP',
        ],
        'currency.number_format' => [
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
        ],
    ]);

    $fee = new GradeLevelFee([
        'tuition_fee_cents' => 123456, // 1234.56
        'miscellaneous_fee_cents' => 50000, // 500.00
        'laboratory_fee_cents' => 25000, // 250.00
        'library_fee_cents' => 10000, // 100.00
        'sports_fee_cents' => 15000, // 150.00
    ]);

    expect($fee->formatted_tuition_fee)->toBe('₱1,234.56');
    expect($fee->formatted_miscellaneous_fee)->toBe('₱500.00');
    expect($fee->formatted_laboratory_fee)->toBe('₱250.00');
    expect($fee->formatted_library_fee)->toBe('₱100.00');
    expect($fee->formatted_sports_fee)->toBe('₱150.00');
    expect($fee->formatted_total_fee)->toBe('₱2,234.56');
});

test('grade level fee model handles zero amounts correctly', function () {
    $fee = new GradeLevelFee;

    $fee->tuition_fee = 0.00;
    expect($fee->tuition_fee_cents)->toBe(0);
    expect($fee->tuition_fee)->toBe(0.00);

    $fee->miscellaneous_fee = 0.00;
    expect($fee->miscellaneous_fee_cents)->toBe(0);
    expect($fee->miscellaneous_fee)->toBe(0.00);
});

test('grade level fee model active scope works correctly', function () {
    GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::KINDER,
        'tuition_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 5000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => true,
    ]);

    GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::GRADE_1,
        'tuition_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 5000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => false,
    ]);

    $activeFees = GradeLevelFee::active()->get();

    expect($activeFees)->toHaveCount(1);
    expect($activeFees->first()->is_active)->toBeTrue();
});

test('grade level fee model current school year scope works correctly', function () {
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;
    $currentSchoolYear = "{$currentYear}-{$nextYear}";
    $pastSchoolYear = ($currentYear - 1).'-'.$currentYear;

    GradeLevelFee::factory()->schoolYear($currentSchoolYear)->create([
        'grade_level' => GradeLevel::KINDER,
        'tuition_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 5000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => true,
    ]);

    GradeLevelFee::factory()->schoolYear($pastSchoolYear)->create([
        'grade_level' => GradeLevel::GRADE_1,
        'tuition_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 5000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => true,
    ]);

    $currentYearFees = GradeLevelFee::currentSchoolYear()->get();

    expect($currentYearFees)->toHaveCount(1);
    expect($currentYearFees->first()->schoolYear->name)->toBe($currentSchoolYear);
});

test('grade level fee model getFeesForGrade method works correctly', function () {
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;
    $currentSchoolYear = "{$currentYear}-{$nextYear}";

    $kindergartenFee = GradeLevelFee::factory()->schoolYear($currentSchoolYear)->create([
        'grade_level' => GradeLevel::KINDER,
        'tuition_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 5000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => true,
    ]);

    GradeLevelFee::factory()->schoolYear($currentSchoolYear)->create([
        'grade_level' => GradeLevel::GRADE_1,
        'tuition_fee_cents' => 110000,
        'miscellaneous_fee_cents' => 5500,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => true,
    ]);

    // Inactive fee for same grade in different school year
    $pastSchoolYear = ($currentYear - 1).'-'.$currentYear;
    GradeLevelFee::factory()->schoolYear($pastSchoolYear)->create([
        'grade_level' => GradeLevel::KINDER,
        'tuition_fee_cents' => 95000,
        'miscellaneous_fee_cents' => 4500,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => false,
    ]);

    $fee = GradeLevelFee::getFeesForGrade(GradeLevel::KINDER);

    expect($fee)->not->toBeNull();
    expect($fee->id)->toBe($kindergartenFee->id);
    expect($fee->grade_level)->toBe(GradeLevel::KINDER);
    expect($fee->is_active)->toBeTrue();
});

test('grade level fee model getFeesForGrade returns null for non-existent grade', function () {
    $fee = GradeLevelFee::getFeesForGrade(GradeLevel::GRADE_6);

    expect($fee)->toBeNull();
});

test('grade level fee model getFeesForGrade works with specific enrollment period', function () {
    $specificSchoolYear = '2024-2025';
    $schoolYear = \App\Models\SchoolYear::firstOrCreate([
        'name' => $specificSchoolYear,
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);

    // Create enrollment period for this school year
    $enrollmentPeriod = \App\Models\EnrollmentPeriod::firstOrCreate([
        'school_year_id' => $schoolYear->id,
    ], [
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'early_registration_deadline' => '2024-05-31',
        'regular_registration_deadline' => '2024-07-31',
        'late_registration_deadline' => '2024-08-31',
        'status' => 'active',
        'description' => "School Year {$specificSchoolYear} Enrollment Period",
        'allow_new_students' => true,
        'allow_returning_students' => true,
    ]);

    $fee = GradeLevelFee::factory()->create([
        'grade_level' => GradeLevel::KINDER,
        'enrollment_period_id' => $enrollmentPeriod->id,
        'tuition_fee_cents' => 100000,
        'miscellaneous_fee_cents' => 5000,
        'laboratory_fee_cents' => 0,
        'library_fee_cents' => 0,
        'sports_fee_cents' => 0,
        'is_active' => true,
    ]);

    $foundFee = GradeLevelFee::getFeesForGrade(GradeLevel::KINDER, $enrollmentPeriod->id);

    expect($foundFee)->not->toBeNull();
    expect($foundFee->id)->toBe($fee->id);
    expect($foundFee->schoolYear->name)->toBe($specificSchoolYear);
});
