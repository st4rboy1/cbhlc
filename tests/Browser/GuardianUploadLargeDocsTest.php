<?php

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('private');
    Notification::fake();
});

describe('Guardian Upload Large Documents', function () {

    test('guardian can upload four documents under 50MB each for Grade 1 student', function () {
        // Create guardian user
        $user = User::factory()->create([
            'email' => 'guardian@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create fake document files - each 40MB to test near-limit uploads
        $birthCert = UploadedFile::fake()->image('birth-cert.jpg', 4000, 4000)->size(40960); // 40MB
        $reportCard = UploadedFile::fake()->image('report-card.jpg', 4000, 4000)->size(40960); // 40MB
        $form138 = UploadedFile::fake()->image('form-138.jpg', 4000, 4000)->size(40960); // 40MB
        $goodMoral = UploadedFile::fake()->image('good-moral.jpg', 4000, 4000)->size(40960); // 40MB

        $this->actingAs($user);

        // Submit student creation form for Grade 1 with all large documents
        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Pedro',
            'middle_name' => 'Garcia',
            'last_name' => 'Santos',
            'birthdate' => '2015-03-20',
            'gender' => 'Male',
            'address' => '456 Test Ave, Manila',
            'contact_number' => '09187654321',
            'email' => 'pedro@example.com',
            'grade_level' => 'Grade 1',
            'birth_place' => 'Quezon City',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => $birthCert,
            'report_card' => $reportCard,
            'form_138' => $form138,
            'good_moral' => $goodMoral,
        ]);

        // Should redirect successfully
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Verify student was created
        $student = Student::where('first_name', 'Pedro')
            ->where('last_name', 'Santos')
            ->first();

        expect($student)->not->toBeNull();
        expect($student->grade_level->value)->toBe('Grade 1');

        // Verify all 4 documents were uploaded
        expect($student->documents()->count())->toBe(4);

        // Verify each document type exists
        expect($student->documents()->where('document_type', 'birth_certificate')->exists())->toBeTrue();
        expect($student->documents()->where('document_type', 'report_card')->exists())->toBeTrue();
        expect($student->documents()->where('document_type', 'form_138')->exists())->toBeTrue();
        expect($student->documents()->where('document_type', 'good_moral')->exists())->toBeTrue();
    })->group('guardian', 'student', 'critical');

    test('guardian can upload documents for Grade 2 student', function () {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create files with reasonable sizes (10MB each)
        $birthCert = UploadedFile::fake()->image('birth-cert.jpg', 2000, 2000)->size(10240);
        $reportCard = UploadedFile::fake()->image('report-card.jpg', 2000, 2000)->size(10240);
        $form138 = UploadedFile::fake()->image('form-138.jpg', 2000, 2000)->size(10240);
        $goodMoral = UploadedFile::fake()->image('good-moral.jpg', 2000, 2000)->size(10240);

        $this->actingAs($user);

        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Maria',
            'middle_name' => 'Lopez',
            'last_name' => 'Reyes',
            'birthdate' => '2014-05-10',
            'gender' => 'Female',
            'address' => '789 Another St, Makati',
            'contact_number' => '09198765432',
            'email' => 'maria@example.com',
            'grade_level' => 'Grade 2',
            'birth_place' => 'Makati',
            'nationality' => 'Filipino',
            'religion' => 'Christian',
            'birth_certificate' => $birthCert,
            'report_card' => $reportCard,
            'form_138' => $form138,
            'good_moral' => $goodMoral,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $student = Student::where('first_name', 'Maria')
            ->where('last_name', 'Reyes')
            ->first();

        expect($student)->not->toBeNull();
        expect($student->grade_level->value)->toBe('Grade 2');
        expect($student->documents()->count())->toBe(4);
    })->group('guardian', 'student', 'critical');

    test('validation requires all documents for Grade 1 and above', function () {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Try to submit Grade 1 student without required documents
        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'birthdate' => '2015-01-01',
            'gender' => 'Male',
            'address' => '123 Test St',
            'grade_level' => 'Grade 1',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => UploadedFile::fake()->image('birth.jpg')->size(1024),
            // Missing report_card, form_138, good_moral
        ]);

        // Should have validation errors for missing documents
        $response->assertSessionHasErrors(['report_card', 'form_138', 'good_moral']);
    })->group('guardian', 'student', 'validation');
});
