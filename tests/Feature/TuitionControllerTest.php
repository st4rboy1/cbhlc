<?php

use App\Enums\GradeLevel;
use App\Models\GradeLevelFee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create school year
    $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
        'name' => '2024-2025',
        'start_year' => 2024,
        'end_year' => 2025,
        'start_date' => '2024-06-01',
        'end_date' => '2025-05-31',
        'status' => 'active',
    ]);
});

describe('tuition controller', function () {
    test('admin can view tuition fees and payment plans', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create grade level fees for current school year
        GradeLevelFee::factory()->create([
            'grade_level' => GradeLevel::GRADE_1,
            'tuition_fee_cents' => 2000000,  // 20000 * 100
            'miscellaneous_fee_cents' => 500000,  // 5000 * 100
            'laboratory_fee_cents' => 200000,  // 2000 * 100
            'library_fee_cents' => 100000,  // 1000 * 100
            'sports_fee_cents' => 50000,  // 500 * 100
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('tuition'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/tuition')
            ->has('gradeLevelFees')
            ->where('gradeLevelFees.'.GradeLevel::GRADE_1->value.'.tuition', 20000)
            ->where('gradeLevelFees.'.GradeLevel::GRADE_1->value.'.miscellaneous', 5000)
            ->has('paymentPlans', 3) // Assert that payment plans are present
        );
    });

    test('guardian can view tuition fees and payment plans', function () {
        $guardian = User::factory()->create();
        $guardian->assignRole('guardian');

        // Create grade level fees for current school year
        GradeLevelFee::factory()->create([
            'grade_level' => GradeLevel::GRADE_1,
            'tuition_fee_cents' => 2000000,  // 20000 * 100
            'miscellaneous_fee_cents' => 500000,  // 5000 * 100
            'is_active' => true,
        ]);

        $response = $this->actingAs($guardian)->get(route('tuition'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/tuition')
            ->has('gradeLevelFees')
            ->has('paymentPlans', 3)
        );
    });

    test('registrar can view tuition fees and payment plans', function () {
        $registrar = User::factory()->create();
        $registrar->assignRole('registrar');

        // Create grade level fees for current school year
        GradeLevelFee::factory()->create([
            'grade_level' => GradeLevel::GRADE_1,
            'tuition_fee_cents' => 2000000,  // 20000 * 100
            'miscellaneous_fee_cents' => 500000,  // 5000 * 100
            'is_active' => true,
        ]);

        $response = $this->actingAs($registrar)->get(route('tuition'));

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shared/tuition')
            ->has('gradeLevelFees')
            ->has('paymentPlans', 3)
        );
    });
});
