<?php

use App\Models\Enrollment;
use App\Models\PaymentReminder;
use App\Models\SchoolYear;
use App\Models\Student;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('can create a payment reminder', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);

    $reminder = PaymentReminder::create([
        'enrollment_id' => $enrollment->id,
        'reminder_type' => 'upcoming_7days',
        'sent_at' => now(),
    ]);

    expect($reminder)->toBeInstanceOf(PaymentReminder::class)
        ->and($reminder->enrollment_id)->toBe($enrollment->id)
        ->and($reminder->reminder_type)->toBe('upcoming_7days')
        ->and($reminder->sent_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('belongs to an enrollment', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);

    $reminder = PaymentReminder::create([
        'enrollment_id' => $enrollment->id,
        'reminder_type' => 'upcoming_3days',
        'sent_at' => now(),
    ]);

    expect($reminder->enrollment)->toBeInstanceOf(Enrollment::class)
        ->and($reminder->enrollment->id)->toBe($enrollment->id);
});

it('casts sent_at as datetime', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);

    $sentAt = now();
    $reminder = PaymentReminder::create([
        'enrollment_id' => $enrollment->id,
        'reminder_type' => 'overdue',
        'sent_at' => $sentAt,
    ]);

    expect($reminder->sent_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($reminder->sent_at->format('Y-m-d H:i:s'))->toBe($sentAt->format('Y-m-d H:i:s'));
});

it('casts email_opened_at as datetime when set', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);

    $openedAt = now();
    $reminder = PaymentReminder::create([
        'enrollment_id' => $enrollment->id,
        'reminder_type' => 'overdue_7days',
        'sent_at' => now()->subHours(2),
        'email_opened_at' => $openedAt,
    ]);

    expect($reminder->email_opened_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($reminder->email_opened_at->format('Y-m-d H:i:s'))->toBe($openedAt->format('Y-m-d H:i:s'));
});

it('has timestamps disabled', function () {
    $reminder = new PaymentReminder;

    expect($reminder->timestamps)->toBeFalse();
});
