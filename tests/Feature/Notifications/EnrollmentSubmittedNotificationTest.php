<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrollmentSubmittedNotification;
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
        'status' => EnrollmentStatus::PENDING,
    ]);

    $notification = new EnrollmentSubmittedNotification($enrollment);

    expect($notification)->toBeInstanceOf(EnrollmentSubmittedNotification::class)
        ->and($notification->enrollment)->toBe($enrollment);
});

it('specifies mail and database as delivery channels', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::PENDING,
    ]);
    $user = User::factory()->create();

    $notification = new EnrollmentSubmittedNotification($enrollment);
    $channels = $notification->via($user);

    expect($channels)->toBe(['mail', 'database']);
});

it('generates correct mail message', function () {
    $student = Student::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $schoolYear = SchoolYear::factory()->create();
    $guardianUser = User::factory()->create();
    $guardianUser->assignRole('guardian');
    $guardian = Guardian::factory()->create(['user_id' => $guardianUser->id]);

    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'guardian_id' => $guardian->id,
        'status' => EnrollmentStatus::PENDING,
    ]);

    $notification = new EnrollmentSubmittedNotification($enrollment);
    $mail = $notification->toMail($guardianUser);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toContain('Enrollment Application Submitted');
});

it('generates correct array representation', function () {
    $student = Student::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'status' => EnrollmentStatus::PENDING,
    ]);
    $user = User::factory()->create();

    $notification = new EnrollmentSubmittedNotification($enrollment);
    $array = $notification->toArray($user);

    expect($array)->toHaveKey('enrollment_id')
        ->and($array['enrollment_id'])->toBe($enrollment->id)
        ->and($array)->toHaveKey('status')
        ->and($array['status'])->toBe('submitted')
        ->and($array)->toHaveKey('student_name')
        ->and($array['student_name'])->toContain('Jane');
});
