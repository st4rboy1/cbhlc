<?php

use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Super Admin Student CRUD', function () {

    test('super admin can successfully create student', function () {
        // Create super admin
        $admin = User::factory()->superAdmin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create guardians
        $guardian1 = Guardian::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Cruz',
        ]);
        $guardian2 = Guardian::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Santos',
        ]);

        $this->actingAs($admin);

        // Submit student creation
        $response = $this->post(route('super-admin.students.store'), [
            'first_name' => 'Pedro',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'birthdate' => '2015-05-15',
            'birth_place' => 'Manila',
            'gender' => 'Male',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'address' => '123 Main St, Manila',
            'phone' => '09123456789',
            'email' => 'pedro@example.com',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [$guardian1->id, $guardian2->id],
        ]);

        // Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect(route('super-admin.students.index'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Student created successfully.');

        // Verify student was created
        $student = Student::where('first_name', 'Pedro')
            ->where('last_name', 'Cruz')
            ->first();

        expect($student)->not->toBeNull();
        expect($student->first_name)->toBe('Pedro');
        expect($student->middle_name)->toBe('Dela');
        expect($student->last_name)->toBe('Cruz');
        expect($student->birthdate->format('Y-m-d'))->toBe('2015-05-15');
        expect($student->grade_level->value)->toBe('Grade 1');

        // Verify guardians are linked
        $guardianLinks = GuardianStudent::where('student_id', $student->id)->get();
        expect($guardianLinks)->toHaveCount(2);

        // First guardian should be primary contact
        $primaryLink = $guardianLinks->where('guardian_id', $guardian1->id)->first();
        expect($primaryLink->is_primary_contact)->toBeTrue();

        // Second guardian should not be primary contact
        $secondaryLink = $guardianLinks->where('guardian_id', $guardian2->id)->first();
        expect($secondaryLink->is_primary_contact)->toBeFalse();
    })->group('super-admin', 'student', 'critical');

    test('super admin can successfully update student', function () {
        $admin = User::factory()->superAdmin()->create();

        // Create existing student with guardian
        $guardian1 = Guardian::factory()->create();
        $guardian2 = Guardian::factory()->create();

        $student = Student::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'birthdate' => '2014-01-01',
            'grade_level' => 'Kinder',
        ]);

        // Link to first guardian
        GuardianStudent::create([
            'student_id' => $student->id,
            'guardian_id' => $guardian1->id,
            'relationship_type' => 'guardian',
            'is_primary_contact' => true,
        ]);

        $this->actingAs($admin);

        // Update student
        $response = $this->put(route('super-admin.students.update', $student), [
            'first_name' => 'Updated',
            'middle_name' => 'New',
            'last_name' => 'Name',
            'birthdate' => '2014-02-02',
            'birth_place' => 'Quezon City',
            'gender' => 'Female',
            'nationality' => 'Filipino',
            'religion' => 'Christian',
            'address' => '456 New Address',
            'phone' => '09187654321',
            'email' => 'updated@example.com',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [$guardian2->id], // Change guardian
        ]);

        // Should redirect successfully
        $response->assertStatus(302);
        $response->assertRedirect(route('super-admin.students.index'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Student updated successfully.');

        // Verify student was updated
        $student->refresh();
        expect($student->first_name)->toBe('Updated');
        expect($student->middle_name)->toBe('New');
        expect($student->birthdate->format('Y-m-d'))->toBe('2014-02-02');
        expect($student->grade_level->value)->toBe('Grade 1');

        // Verify guardians were synced
        $guardianLinks = GuardianStudent::where('student_id', $student->id)->get();
        expect($guardianLinks)->toHaveCount(1);
        expect($guardianLinks->first()->guardian_id)->toBe($guardian2->id);
        expect($guardianLinks->first()->is_primary_contact)->toBeTrue();
    })->group('super-admin', 'student', 'critical');

    test('validation fails when creating student without required fields', function () {
        $admin = User::factory()->superAdmin()->create();
        $guardian = Guardian::factory()->create();

        $this->actingAs($admin);

        // Try to create student with missing required fields
        $response = $this->post(route('super-admin.students.store'), [
            'first_name' => 'Test',
            // Missing last_name, birthdate, gender, address, grade_level, guardian_ids
        ]);

        // Should have validation errors
        $response->assertSessionHasErrors([
            'last_name',
            'birthdate',
            'gender',
            'address',
            'grade_level',
            'guardian_ids',
        ]);
    })->group('super-admin', 'student', 'validation');

    test('validation fails when creating student with future birthdate', function () {
        $admin = User::factory()->superAdmin()->create();
        $guardian = Guardian::factory()->create();

        $this->actingAs($admin);

        $response = $this->post(route('super-admin.students.store'), [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'birthdate' => now()->addDay()->format('Y-m-d'), // Future date
            'gender' => 'Male',
            'address' => '123 Test St',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [$guardian->id],
        ]);

        $response->assertSessionHasErrors(['birthdate']);
    })->group('super-admin', 'student', 'validation');

    test('validation fails when creating student without guardians', function () {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin);

        $response = $this->post(route('super-admin.students.store'), [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'birthdate' => '2015-01-01',
            'gender' => 'Male',
            'address' => '123 Test St',
            'grade_level' => 'Grade 1',
            'guardian_ids' => [], // Empty guardians
        ]);

        $response->assertSessionHasErrors(['guardian_ids']);
    })->group('super-admin', 'student', 'validation');
});
