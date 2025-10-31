<?php

use App\Models\Guardian;
use App\Models\GuardianStudent;
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

describe('Guardian Add Student', function () {

    test('guardian can successfully add student with all documents', function () {
        // Create guardian user
        $user = User::factory()->create([
            'email' => 'guardian@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create fake document files
        $birthCert = UploadedFile::fake()->image('birth-cert.jpg', 1000, 1000)->size(1024); // 1MB
        $reportCard = UploadedFile::fake()->image('report-card.jpg', 1000, 1000)->size(1024);
        $form138 = UploadedFile::fake()->image('form-138.jpg', 1000, 1000)->size(1024);
        $goodMoral = UploadedFile::fake()->image('good-moral.jpg', 1000, 1000)->size(1024);

        $this->actingAs($user);

        // Submit student creation form for Grade 1
        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'birthdate' => '2015-01-15',
            'gender' => 'Male',
            'address' => '123 Main St, Manila',
            'contact_number' => '09123456789',
            'email' => 'juan@example.com',
            'grade_level' => 'Grade 1',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => $birthCert,
            'report_card' => $reportCard,
            'form_138' => $form138,
            'good_moral' => $goodMoral,
        ]);

        // Should redirect successfully (not 500 error)
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Verify student was created
        $student = Student::where('first_name', 'Juan')
            ->where('last_name', 'Cruz')
            ->first();

        expect($student)->not->toBeNull();
        expect($student->first_name)->toBe('Juan');
        expect($student->grade_level->value)->toBe('Grade 1');

        // Verify student is linked to guardian
        $guardianStudent = GuardianStudent::where('guardian_id', $guardian->id)
            ->where('student_id', $student->id)
            ->first();

        expect($guardianStudent)->not->toBeNull();

        // Verify documents were uploaded
        expect($student->documents()->count())->toBe(4);

        // Check specific documents exist
        expect($student->documents()->where('document_type', 'birth_certificate')->exists())->toBeTrue();
        expect($student->documents()->where('document_type', 'report_card')->exists())->toBeTrue();
        expect($student->documents()->where('document_type', 'form_138')->exists())->toBeTrue();
        expect($student->documents()->where('document_type', 'good_moral')->exists())->toBeTrue();
    })->group('guardian', 'student', 'critical');

    test('guardian can add kinder student without optional documents', function () {
        $user = User::factory()->create([
            'email' => 'guardian@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        $birthCert = UploadedFile::fake()->image('birth-cert.jpg')->size(1024);

        $this->actingAs($user);

        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Reyes',
            'birthdate' => '2018-03-20',
            'gender' => 'Female',
            'address' => '456 Side St, Quezon City',
            'contact_number' => '09187654321',
            'email' => 'maria@example.com',
            'grade_level' => 'Kinder',
            'birth_place' => 'Quezon City',
            'nationality' => 'Filipino',
            'religion' => 'Christian',
            'birth_certificate' => $birthCert,
            // Optional documents not provided for Kinder
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $student = Student::where('first_name', 'Maria')
            ->where('last_name', 'Reyes')
            ->first();

        expect($student)->not->toBeNull();
        expect($student->grade_level->value)->toBe('Kinder');

        // Only birth certificate uploaded
        expect($student->documents()->count())->toBe(1);
        expect($student->documents()->where('document_type', 'birth_certificate')->exists())->toBeTrue();
    })->group('guardian', 'student', 'critical');

    test('student creation does not fail if notification fails', function () {
        // Don't create any registrars - notification will have no one to send to
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        $birthCert = UploadedFile::fake()->image('birth-cert.jpg')->size(1024);
        $reportCard = UploadedFile::fake()->image('report-card.jpg')->size(1024);
        $form138 = UploadedFile::fake()->image('form-138.jpg')->size(1024);
        $goodMoral = UploadedFile::fake()->image('good-moral.jpg')->size(1024);

        $this->actingAs($user);

        // This should succeed even though there are no registrars to notify
        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Test',
            'middle_name' => 'Middle',
            'last_name' => 'Student',
            'birthdate' => '2016-06-10',
            'gender' => 'Male',
            'address' => '789 Test Ave',
            'contact_number' => '09111222333',
            'email' => 'test@example.com',
            'grade_level' => 'Grade 2',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => $birthCert,
            'report_card' => $reportCard,
            'form_138' => $form138,
            'good_moral' => $goodMoral,
        ]);

        // Should still succeed (no 500 error)
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Student should be created
        $student = Student::where('first_name', 'Test')->first();
        expect($student)->not->toBeNull();
    })->group('guardian', 'student', 'critical');

    test('validation fails when required documents missing for grade 1 and above', function () {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        $birthCert = UploadedFile::fake()->image('birth-cert.jpg')->size(1024);

        $this->actingAs($user);

        // Try to submit Grade 1 student without required documents
        $response = $this->post(route('guardian.students.store'), [
            'first_name' => 'Incomplete',
            'middle_name' => 'Doc',
            'last_name' => 'Student',
            'birthdate' => '2015-01-15',
            'gender' => 'Male',
            'address' => '123 Main St',
            'contact_number' => '09123456789',
            'email' => 'incomplete@example.com',
            'grade_level' => 'Grade 1',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'birth_certificate' => $birthCert,
            // Missing report_card, form_138, good_moral
        ]);

        // Should fail validation
        $response->assertSessionHasErrors(['report_card', 'form_138', 'good_moral']);
    })->group('guardian', 'student', 'validation');
});
