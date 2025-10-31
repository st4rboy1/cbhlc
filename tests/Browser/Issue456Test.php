<?php

use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('update student button works with young student (under 18)', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student linked to this guardian (under 18 years old)
    $student = Student::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => 'Old Address',
        'birthdate' => now()->subYears(10)->format('Y-m-d'), // 10 years old
    ]);

    GuardianStudent::create([
        'guardian_id' => $guardianModel->id,
        'student_id' => $student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Try to update the student
    $response = actingAs($user)
        ->put("/guardian/students/{$student->id}", [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'middle_name' => '',
            'birthdate' => $student->birthdate,
            'gender' => $student->gender,
            'contact_number' => $student->contact_number,
            'email' => $student->email,
            'address' => 'New Address',
            'birth_place' => $student->birth_place,
            'nationality' => $student->nationality,
            'religion' => $student->religion,
        ]);

    // Should redirect to show page with success message
    $response->assertRedirect("/guardian/students/{$student->id}");
    $response->assertSessionHas('success');

    // Verify database was updated
    $student->refresh();
    expect($student->first_name)->toBe('Jane')
        ->and($student->last_name)->toBe('Smith')
        ->and($student->address)->toBe('New Address');
})->group('browser', 'bug', 'issue-456');

test('update student button works with 18-year-old student (fixed)', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student linked to this guardian (18 years old - should now work!)
    $student = Student::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => 'Old Address',
        'birthdate' => now()->subYears(18)->format('Y-m-d'), // Exactly 18 years old
    ]);

    GuardianStudent::create([
        'guardian_id' => $guardianModel->id,
        'student_id' => $student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Try to update the student - should now work after fixing age validation
    $response = actingAs($user)
        ->put("/guardian/students/{$student->id}", [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'middle_name' => '',
            'birthdate' => $student->birthdate,  // 18 years old - should work now!
            'gender' => $student->gender,
            'contact_number' => $student->contact_number,
            'email' => $student->email,
            'address' => 'New Address',
            'birth_place' => $student->birth_place,
            'nationality' => $student->nationality,
            'religion' => $student->religion,
        ]);

    // Should redirect to show page with success message
    $response->assertRedirect("/guardian/students/{$student->id}");
    $response->assertSessionHas('success');

    // Verify database was updated
    $student->refresh();
    expect($student->first_name)->toBe('Jane')
        ->and($student->last_name)->toBe('Smith')
        ->and($student->address)->toBe('New Address');
})->group('browser', 'bug', 'issue-456');
