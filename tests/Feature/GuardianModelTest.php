<?php

use App\Models\Guardian;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guardian model can be created', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'John',
        'middle_name' => 'Michael',
        'last_name' => 'Doe',
        'phone' => '09123456789',
        'address' => '123 Test Street',
        'occupation' => 'Teacher',
        'employer' => 'Test School',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '09987654321',
        'emergency_contact_relationship' => 'Spouse',
    ]);

    expect($guardian)->toBeInstanceOf(Guardian::class);
    expect($guardian->first_name)->toBe('John');
    expect($guardian->middle_name)->toBe('Michael');
    expect($guardian->last_name)->toBe('Doe');
    expect($guardian->phone)->toBe('09123456789');
    expect($guardian->address)->toBe('123 Test Street');
    expect($guardian->occupation)->toBe('Teacher');
    expect($guardian->employer)->toBe('Test School');
    expect($guardian->emergency_contact_name)->toBe('Jane Doe');
    expect($guardian->emergency_contact_phone)->toBe('09987654321');
    expect($guardian->emergency_contact_relationship)->toBe('Spouse');
});

test('guardian belongs to user', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '09123456789',
        'address' => '123 Test Street',
    ]);

    expect($guardian->user)->toBeInstanceOf(User::class);
    expect($guardian->user->id)->toBe($user->id);
});

test('guardian can have many children', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '09123456789',
        'address' => '123 Test Street',
    ]);

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    GuardianStudent::create([
        'guardian_id' => $user->id,  // Use user id, not guardian model id
        'student_id' => $student1->id,
        'relationship_type' => 'father',
        'is_primary_contact' => true,
    ]);

    GuardianStudent::create([
        'guardian_id' => $user->id,  // Use user id, not guardian model id
        'student_id' => $student2->id,
        'relationship_type' => 'father',
        'is_primary_contact' => true,
    ]);

    $children = $guardian->children()->get();

    expect($children)->toHaveCount(2);
    expect($children->pluck('id')->contains($student1->id))->toBeTrue();
    expect($children->pluck('id')->contains($student2->id))->toBeTrue();
});

test('guardian children relationship includes pivot data', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone' => '09123456789',
        'address' => '456 Test Avenue',
    ]);

    $student = Student::factory()->create();

    GuardianStudent::create([
        'guardian_id' => $user->id,  // Use user id, not guardian model id
        'student_id' => $student->id,
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    $child = $guardian->children()->first();

    expect($child->relationship_type)->toBe('mother');
    expect($child->is_primary_contact)->toBe(1);
});

test('guardian model fillable attributes work correctly', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $data = [
        'user_id' => $user->id,
        'first_name' => 'Test',
        'middle_name' => 'Middle',
        'last_name' => 'Guardian',
        'phone' => '09111111111',
        'address' => '789 Test Boulevard',
        'occupation' => 'Engineer',
        'employer' => 'Tech Company',
        'emergency_contact_name' => 'Emergency Contact',
        'emergency_contact_phone' => '09222222222',
        'emergency_contact_relationship' => 'Sibling',
    ];

    $guardian = Guardian::create($data);

    foreach ($data as $key => $value) {
        expect($guardian->$key)->toBe($value);
    }
});
