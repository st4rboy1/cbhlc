<?php

use App\Enums\RelationshipType;
use App\Models\GuardianStudent;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guardian can have children through guardian_students pivot table', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = \App\Models\Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '09123456789',
        'address' => '123 Test St',
    ]);

    $student = Student::factory()->create();

    // Create the relationship
    GuardianStudent::create([
        'guardian_id' => $user->id,
        'student_id' => $student->id,
        'relationship_type' => RelationshipType::MOTHER->value,
        'is_primary_contact' => true,
    ]);

    $children = $guardian->children()->get();

    expect($children)->toHaveCount(1);
    expect($children->first()->id)->toBe($student->id);
    expect($children->first()->relationship_type)->toBe('mother');
    expect($children->first()->is_primary_contact)->toBe(1);
});

test('student can have multiple guardians', function () {
    $mother = User::factory()->create();
    $mother->assignRole('guardian');

    $father = User::factory()->create();
    $father->assignRole('guardian');

    $student = Student::factory()->create();

    // Create relationships
    GuardianStudent::create([
        'guardian_id' => $mother->id,
        'student_id' => $student->id,
        'relationship_type' => RelationshipType::MOTHER->value,
        'is_primary_contact' => true,
    ]);

    GuardianStudent::create([
        'guardian_id' => $father->id,
        'student_id' => $student->id,
        'relationship_type' => RelationshipType::FATHER->value,
        'is_primary_contact' => false,
    ]);

    $parents = $student->guardians()->get();

    expect($parents)->toHaveCount(2);

    $motherRelation = $parents->where('id', $mother->id)->first();
    expect($motherRelation->pivot->relationship_type)->toBe('mother');
    expect($motherRelation->pivot->is_primary_contact)->toBe(1);

    $fatherRelation = $parents->where('id', $father->id)->first();
    expect($fatherRelation->pivot->relationship_type)->toBe('father');
    expect($fatherRelation->pivot->is_primary_contact)->toBe(0);
});

test('guardian can have multiple children', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = \App\Models\Guardian::create([
        'user_id' => $user->id,
        'first_name' => 'John',
        'last_name' => 'Smith',
        'phone' => '09123456789',
        'address' => '456 Test Ave',
    ]);

    $child1 = Student::factory()->create();
    $child2 = Student::factory()->create();

    // Create relationships
    GuardianStudent::create([
        'guardian_id' => $user->id,
        'student_id' => $child1->id,
        'relationship_type' => RelationshipType::GUARDIAN->value,
        'is_primary_contact' => true,
    ]);

    GuardianStudent::create([
        'guardian_id' => $user->id,
        'student_id' => $child2->id,
        'relationship_type' => RelationshipType::GUARDIAN->value,
        'is_primary_contact' => false,
    ]);

    $children = $guardian->children()->get();

    expect($children)->toHaveCount(2);
    expect($children->pluck('id')->toArray())->toContain($child1->id, $child2->id);
});

test('guardian student relationship uses correct table and column names', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    $student = Student::factory()->create();

    GuardianStudent::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'relationship_type' => RelationshipType::GRANDPARENT->value,
        'is_primary_contact' => false,
    ]);

    // Verify the relationship exists in the correct table
    $this->assertDatabaseHas('guardian_students', [
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'relationship_type' => 'grandparent',
        'is_primary_contact' => false,
    ]);

    // Verify old table doesn't exist
    $this->expectException(\Illuminate\Database\QueryException::class);
    \DB::table('parent_students')->count();
});

test('guardian student model relationships work correctly', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    $student = Student::factory()->create();

    $relationship = GuardianStudent::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'relationship_type' => RelationshipType::OTHER->value,
        'is_primary_contact' => true,
    ]);

    expect($relationship->guardian->id)->toBe($guardian->id);
    expect($relationship->student->id)->toBe($student->id);
    expect($relationship->relationship_type)->toBe('other');
    expect($relationship->is_primary_contact)->toBeTrue();
});

test('relationship type enum values are properly validated', function () {
    $guardian = User::factory()->create();
    $guardian->assignRole('guardian');

    $student = Student::factory()->create();

    // Test valid relationship types
    $validTypes = RelationshipType::values();

    foreach ($validTypes as $type) {
        $relationship = GuardianStudent::create([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relationship_type' => $type,
            'is_primary_contact' => false,
        ]);

        expect($relationship->relationship_type)->toBe($type);
        $relationship->delete(); // Clean up for next iteration
    }
});
