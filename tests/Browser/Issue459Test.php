<?php

use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('guardian can remove student from their account (no active enrollments)', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student linked to this guardian
    $student = Student::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    GuardianStudent::create([
        'guardian_id' => $guardianModel->id,
        'student_id' => $student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Try to delete the student (remove from guardian's account)
    $response = actingAs($user)
        ->delete("/guardian/students/{$student->id}");

    // Should redirect to students index with success message
    $response->assertRedirect('/guardian/students');
    $response->assertSessionHas('success', 'Student removed from your account successfully.');

    // Verify the guardian-student relationship was removed
    expect(GuardianStudent::where('guardian_id', $guardianModel->id)
        ->where('student_id', $student->id)
        ->exists())->toBeFalse();

    // Verify the student still exists in the system
    expect(Student::find($student->id))->not->toBeNull();
})->group('browser', 'bug', 'issue-459');

test('guardian cannot remove student with active enrollments', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student with an active enrollment
    $student = Student::factory()->create();

    GuardianStudent::create([
        'guardian_id' => $guardianModel->id,
        'student_id' => $student->id,
        'relationship_type' => 'father',
        'is_primary_contact' => true,
    ]);

    // Create an active enrollment
    \App\Models\Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardianModel->id,
        'status' => \App\Enums\EnrollmentStatus::ENROLLED,
    ]);

    // Try to delete the student
    $response = actingAs($user)
        ->delete("/guardian/students/{$student->id}");

    // Should redirect back with error message
    $response->assertRedirect("/guardian/students/{$student->id}");
    $response->assertSessionHas('error', 'Cannot remove student with active or pending enrollments.');

    // Verify the guardian-student relationship still exists
    expect(GuardianStudent::where('guardian_id', $guardianModel->id)
        ->where('student_id', $student->id)
        ->exists())->toBeTrue();
})->group('browser', 'bug', 'issue-459');
