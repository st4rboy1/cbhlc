<?php

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new StudentPolicy;
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin can view any students', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('administrator can view any students', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('registrar can view any students', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('guardian can view any students', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('student cannot view any students', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('super admin can view a student', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $student = Student::factory()->create();

    expect($this->policy->view($user, $student))->toBeTrue();
});

test('administrator can view a student', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $student = Student::factory()->create();

    expect($this->policy->view($user, $student))->toBeTrue();
});

test('registrar can view a student', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $student = Student::factory()->create();

    expect($this->policy->view($user, $student))->toBeTrue();
});

test('guardian can view their own student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $guardian = Guardian::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->create();
    $student->guardians()->attach($guardian->id);

    expect($this->policy->view($user, $student))->toBeTrue();
});

test('guardian cannot view another guardian student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    Guardian::factory()->create(['user_id' => $user->id]);

    $otherGuardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $student->guardians()->attach($otherGuardian->id);

    expect($this->policy->view($user, $student))->toBeFalse();
});

test('student can view their own record', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    $student = Student::factory()->create(['user_id' => $user->id]);

    expect($this->policy->view($user, $student))->toBeTrue();
});

test('student cannot view another student record', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    Student::factory()->create(['user_id' => $user->id]);

    $otherStudent = Student::factory()->create();

    expect($this->policy->view($user, $otherStudent))->toBeFalse();
});

test('super admin can create students', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

test('administrator can create students', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->create($user))->toBeTrue();
});

test('registrar can create students', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->create($user))->toBeTrue();
});

test('guardian can create students', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    expect($this->policy->create($user))->toBeTrue();
});

test('student cannot create students', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    expect($this->policy->create($user))->toBeFalse();
});

test('super admin can update a student', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $student = Student::factory()->create();

    expect($this->policy->update($user, $student))->toBeTrue();
});

test('administrator can update a student', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $student = Student::factory()->create();

    expect($this->policy->update($user, $student))->toBeTrue();
});

test('registrar can update a student', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $student = Student::factory()->create();

    expect($this->policy->update($user, $student))->toBeTrue();
});

test('guardian can update their own student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $guardian = Guardian::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->create();
    $student->guardians()->attach($guardian->id);

    expect($this->policy->update($user, $student))->toBeTrue();
});

test('guardian cannot update another guardian student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    Guardian::factory()->create(['user_id' => $user->id]);

    $otherGuardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $student->guardians()->attach($otherGuardian->id);

    expect($this->policy->update($user, $student))->toBeFalse();
});

test('student cannot update students', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    $student = Student::factory()->create();

    expect($this->policy->update($user, $student))->toBeFalse();
});

test('super admin can delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $student = Student::factory()->create();

    expect($this->policy->delete($user, $student))->toBeTrue();
});

test('administrator can delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $student = Student::factory()->create();

    expect($this->policy->delete($user, $student))->toBeTrue();
});

test('registrar cannot delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $student = Student::factory()->create();

    expect($this->policy->delete($user, $student))->toBeFalse();
});

test('guardian can delete their own student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $guardian = Guardian::factory()->create(['user_id' => $user->id]);
    $student = Student::factory()->create();
    $student->guardians()->attach($guardian->id);

    expect($this->policy->delete($user, $student))->toBeTrue();
});

test('guardian cannot delete another guardian student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    Guardian::factory()->create(['user_id' => $user->id]);

    $otherGuardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $student->guardians()->attach($otherGuardian->id);

    expect($this->policy->delete($user, $student))->toBeFalse();
});

test('student cannot delete students', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    $student = Student::factory()->create();

    expect($this->policy->delete($user, $student))->toBeFalse();
});

test('super admin can restore a student', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $student = Student::factory()->create();

    expect($this->policy->restore($user, $student))->toBeTrue();
});

test('administrator can restore a student', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $student = Student::factory()->create();

    expect($this->policy->restore($user, $student))->toBeTrue();
});

test('registrar cannot restore a student', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $student = Student::factory()->create();

    expect($this->policy->restore($user, $student))->toBeFalse();
});

test('guardian cannot restore a student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $student = Student::factory()->create();

    expect($this->policy->restore($user, $student))->toBeFalse();
});

test('student cannot restore a student', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    $student = Student::factory()->create();

    expect($this->policy->restore($user, $student))->toBeFalse();
});

test('super admin can force delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $student = Student::factory()->create();

    expect($this->policy->forceDelete($user, $student))->toBeTrue();
});

test('administrator cannot force delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $student = Student::factory()->create();

    expect($this->policy->forceDelete($user, $student))->toBeFalse();
});

test('registrar cannot force delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $student = Student::factory()->create();

    expect($this->policy->forceDelete($user, $student))->toBeFalse();
});

test('guardian cannot force delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $student = Student::factory()->create();

    expect($this->policy->forceDelete($user, $student))->toBeFalse();
});

test('student cannot force delete a student', function () {
    $user = User::factory()->create();
    $user->assignRole('student');
    $student = Student::factory()->create();

    expect($this->policy->forceDelete($user, $student))->toBeFalse();
});
