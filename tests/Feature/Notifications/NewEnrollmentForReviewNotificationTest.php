<?php

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Notifications\NewEnrollmentForReviewNotification;
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
    ]);

    $notification = new NewEnrollmentForReviewNotification($enrollment);

    expect($notification)->toBeInstanceOf(NewEnrollmentForReviewNotification::class)
        ->and($notification->enrollment)->toBe($enrollment);
});

it('specifies mail and database as delivery channels', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $user = User::factory()->create();

    $notification = new NewEnrollmentForReviewNotification($enrollment);
    $channels = $notification->via($user);

    expect($channels)->toBe(['mail', 'database']);
});

it('generates correct mail message', function () {
    $student = Student::factory()->create();
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $user = User::factory()->create(['name' => 'John Registrar']);

    $notification = new NewEnrollmentForReviewNotification($enrollment);
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('New Enrollment Application Requires Review')
        ->and($mail->greeting)->toContain('Hello John Registrar');
});

it('generates correct array representation', function () {
    $student = Student::factory()->create(['first_name' => 'Jane', 'last_name' => 'Student']);
    $schoolYear = SchoolYear::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
    ]);
    $user = User::factory()->create();

    $notification = new NewEnrollmentForReviewNotification($enrollment);
    $array = $notification->toArray($user);

    expect($array)->toHaveKey('enrollment_id')
        ->and($array['enrollment_id'])->toBe($enrollment->id)
        ->and($array)->toHaveKey('student_name')
        ->and($array['student_name'])->toContain('Jane')
        ->and($array)->toHaveKey('status')
        ->and($array['status'])->toBe('pending_review');
});
