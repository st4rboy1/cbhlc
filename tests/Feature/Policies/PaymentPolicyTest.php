<?php

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Policies\PaymentPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new PaymentPolicy;
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows super_admin to view any payments', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows administrator to view any payments', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows registrar to view any payments', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows guardian to view any payments', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('denies other roles from viewing any payments', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    expect($this->policy->viewAny($user))->toBeFalse();
});

it('allows super_admin to view a payment', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $guardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    expect($this->policy->view($user, $payment))->toBeTrue();
});

it('allows administrator to view a payment', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    $guardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    expect($this->policy->view($user, $payment))->toBeTrue();
});

it('allows registrar to view a payment', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    $guardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    expect($this->policy->view($user, $payment))->toBeTrue();
});

it('allows guardian to view their own payment', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    $guardian = Guardian::factory()->create([
        'user_id' => $user->id,
    ]);
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    expect($this->policy->view($user, $payment))->toBeTrue();
});

it('denies guardian from viewing another guardian payment', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');
    Guardian::factory()->create([
        'user_id' => $user->id,
    ]);

    $otherGuardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $otherGuardian->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    expect($this->policy->view($user, $payment))->toBeFalse();
});

it('denies other roles from viewing a payment', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $guardian = Guardian::factory()->create();
    $student = Student::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
    ]);
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);
    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    expect($this->policy->view($user, $payment))->toBeFalse();
});
