<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrollmentApprovedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Notifications\Messages\MailMessage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('can be instantiated with an enrollment', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::APPROVED,
        'approved_at' => now(),
    ]);

    $notification = new EnrollmentApprovedNotification($enrollment);

    expect($notification)->toBeInstanceOf(EnrollmentApprovedNotification::class)
        ->and($notification->enrollment)->toBe($enrollment)
        ->and($notification->remarks)->toBeNull();
});

it('can be instantiated with remarks', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::APPROVED,
        'approved_at' => now(),
    ]);

    $notification = new EnrollmentApprovedNotification($enrollment, 'Welcome to our school!');

    expect($notification->remarks)->toBe('Welcome to our school!');
});

it('specifies mail and database as delivery channels', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::APPROVED,
        'approved_at' => now(),
    ]);
    $user = User::factory()->create();

    $notification = new EnrollmentApprovedNotification($enrollment);
    $channels = $notification->via($user);

    expect($channels)->toBe(['mail', 'database']);
});

it('generates correct mail message without remarks', function () {
    $student = Student::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::APPROVED,
        'approved_at' => now(),
    ]);
    $user = User::factory()->create();

    $notification = new EnrollmentApprovedNotification($enrollment);
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toContain('Enrollment Application Approved');
});

it('generates correct mail message with remarks', function () {
    $student = Student::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::APPROVED,
        'approved_at' => now(),
    ]);
    $user = User::factory()->create();

    $notification = new EnrollmentApprovedNotification($enrollment, 'Great application!');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class);
});

it('generates correct array representation', function () {
    $student = Student::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::APPROVED,
        'approved_at' => now(),
    ]);
    $user = User::factory()->create();

    $notification = new EnrollmentApprovedNotification($enrollment, 'Test remarks');
    $array = $notification->toArray($user);

    expect($array)->toHaveKey('enrollment_id')
        ->and($array['enrollment_id'])->toBe($enrollment->id)
        ->and($array)->toHaveKey('status')
        ->and($array['status'])->toBe('approved')
        ->and($array)->toHaveKey('remarks')
        ->and($array['remarks'])->toBe('Test remarks')
        ->and($array)->toHaveKey('student_name')
        ->and($array['student_name'])->toContain('Jane');
});
