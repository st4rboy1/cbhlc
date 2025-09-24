<?php

namespace Tests\Feature\Services;

use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Mail\EnrollmentSubmitted;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private EnrollmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EnrollmentService::class);
        Mail::fake();
    }

    public function test_sends_email_when_enrollment_is_submitted(): void
    {
        // Arrange
        $guardian = User::factory()->create(['email' => 'guardian@test.com']);
        $student = Student::factory()->create(['guardian_id' => $guardian->id]);

        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => 'Grade 1',
            'section' => 'A',
        ];

        // Act
        $enrollment = $this->service->createEnrollment($enrollmentData);

        // Assert
        Mail::assertQueued(EnrollmentSubmitted::class, function ($mail) use ($guardian, $enrollment) {
            return $mail->hasTo($guardian->email) &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }

    public function test_sends_email_when_enrollment_is_approved(): void
    {
        // Arrange
        $guardian = User::factory()->create(['email' => 'guardian@test.com']);
        $student = Student::factory()->create(['guardian_id' => $guardian->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->create());

        // Act
        $this->service->approveEnrollment($enrollment);

        // Assert
        Mail::assertQueued(EnrollmentApproved::class, function ($mail) use ($guardian, $enrollment) {
            return $mail->hasTo($guardian->email) &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }

    public function test_sends_email_when_enrollment_is_rejected(): void
    {
        // Arrange
        $guardian = User::factory()->create(['email' => 'guardian@test.com']);
        $student = Student::factory()->create(['guardian_id' => $guardian->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => 'pending',
        ]);

        $reason = 'Missing required documents';
        $this->actingAs(User::factory()->create());

        // Act
        $this->service->rejectEnrollment($enrollment, $reason);

        // Assert
        Mail::assertQueued(EnrollmentRejected::class, function ($mail) use ($guardian, $enrollment, $reason) {
            return $mail->hasTo($guardian->email) &&
                   $mail->enrollment->id === $enrollment->id &&
                   $mail->reason === $reason;
        });
    }

    public function test_sends_multiple_emails_when_bulk_approving_enrollments(): void
    {
        // Arrange
        $guardians = User::factory()->count(3)->create();
        $enrollments = [];

        foreach ($guardians as $guardian) {
            $student = Student::factory()->create(['guardian_id' => $guardian->id]);
            $enrollments[] = Enrollment::factory()->create([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'status' => 'pending',
            ]);
        }

        $enrollmentIds = collect($enrollments)->pluck('id')->toArray();
        $this->actingAs(User::factory()->create());

        // Act
        $count = $this->service->bulkApproveEnrollments($enrollmentIds);

        // Assert
        $this->assertEquals(3, $count);
        Mail::assertQueuedCount(3);

        foreach ($enrollments as $index => $enrollment) {
            Mail::assertQueued(EnrollmentApproved::class, function ($mail) use ($guardians, $enrollment, $index) {
                return $mail->hasTo($guardians[$index]->email) &&
                       $mail->enrollment->id === $enrollment->id;
            });
        }
    }

    public function test_does_not_send_email_when_guardian_has_no_email(): void
    {
        // Arrange
        $guardian = User::factory()->create(['email' => '']);
        $student = Student::factory()->create(['guardian_id' => $guardian->id]);

        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => 'Grade 1',
            'section' => 'A',
        ];

        // Act
        $this->service->createEnrollment($enrollmentData);

        // Assert
        Mail::assertNothingQueued();
    }

    public function test_email_contains_correct_enrollment_details(): void
    {
        // Arrange
        $guardian = User::factory()->create(['email' => 'guardian@test.com']);
        $student = Student::factory()->create([
            'guardian_id' => $guardian->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year' => '2024-2025',
            'grade_level' => 'Grade 5',
            'section' => 'B',
        ];

        // Act
        $enrollment = $this->service->createEnrollment($enrollmentData);

        // Assert
        Mail::assertQueued(EnrollmentSubmitted::class, function ($mail) use ($enrollment) {
            // Check if mail is for the correct recipient and enrollment
            return $mail->hasTo('guardian@test.com') &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }
}
