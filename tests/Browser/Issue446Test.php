<?php

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');
});

test('guardian can create kindergarten student without optional documents', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    Guardian::factory()->create(['user_id' => $user->id]);

    // Try to create a Kinder student with only birth certificate
    $response = actingAs($user)
        ->post('/guardian/students', [
            'first_name' => 'John',
            'middle_name' => 'Middle',
            'last_name' => 'Doe',
            'birthdate' => now()->subYears(5)->format('Y-m-d'),
            'gender' => 'Male',
            'address' => '123 Test Street',
            'contact_number' => '09123456789',
            'email' => 'john.doe@example.com',
            'grade_level' => 'Kinder',
            'birth_place' => 'Test City',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => UploadedFile::fake()->image('birth_cert.jpg'),
            // Note: Not including report_card, form_138, good_moral - should be optional for Kinder
        ]);

    // Should redirect to student show page with success message
    $response->assertRedirect()
        ->assertSessionHas('success', 'Student and documents added successfully.');

    // Verify student was created
    $this->assertDatabaseHas('students', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'grade_level' => 'Kinder',
    ]);
})->group('browser', 'bug', 'issue-446');

test('guardian must provide all documents for Grade 1 student', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    Guardian::factory()->create(['user_id' => $user->id]);

    // Try to create a Grade 1 student without optional documents - should fail
    $response = actingAs($user)
        ->post('/guardian/students', [
            'first_name' => 'Jane',
            'middle_name' => 'Middle',
            'last_name' => 'Doe',
            'birthdate' => now()->subYears(7)->format('Y-m-d'),
            'gender' => 'Female',
            'address' => '123 Test Street',
            'contact_number' => '09123456789',
            'email' => 'jane.doe@example.com',
            'grade_level' => 'Grade 1',
            'birth_place' => 'Test City',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => UploadedFile::fake()->image('birth_cert.jpg'),
            // Note: Missing report_card, form_138, good_moral - should be REQUIRED for Grade 1
        ]);

    // Should fail validation and return with errors
    $response->assertSessionHasErrors(['report_card', 'form_138', 'good_moral']);
})->group('browser', 'bug', 'issue-446');

test('guardian can create Grade 1 student with all required documents', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');

    Guardian::factory()->create(['user_id' => $user->id]);

    // Try to create a Grade 1 student with ALL documents
    $response = actingAs($user)
        ->post('/guardian/students', [
            'first_name' => 'Jane',
            'middle_name' => 'Middle',
            'last_name' => 'Doe',
            'birthdate' => now()->subYears(7)->format('Y-m-d'),
            'gender' => 'Female',
            'address' => '123 Test Street',
            'contact_number' => '09123456789',
            'email' => 'jane.doe@example.com',
            'grade_level' => 'Grade 1',
            'birth_place' => 'Test City',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => UploadedFile::fake()->image('birth_cert.jpg'),
            'report_card' => UploadedFile::fake()->image('report_card.jpg'),
            'form_138' => UploadedFile::fake()->image('form_138.jpg'),
            'good_moral' => UploadedFile::fake()->image('good_moral.jpg'),
        ]);

    // Should redirect to student show page with success message
    $response->assertRedirect()
        ->assertSessionHas('success', 'Student and documents added successfully.');

    // Verify student was created
    $this->assertDatabaseHas('students', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'grade_level' => 'Grade 1',
    ]);

    // Verify all 4 documents were uploaded for Grade 1 student
    $student = \App\Models\Student::where('first_name', 'Jane')->where('last_name', 'Doe')->first();
    expect($student->documents()->count())->toBe(4); // birth_cert, report_card, form_138, good_moral
})->group('browser', 'bug', 'issue-446');
