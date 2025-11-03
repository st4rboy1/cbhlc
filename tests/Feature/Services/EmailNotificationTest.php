<?php

namespace Tests\Feature\Services;

use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Mail\EnrollmentSubmitted;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrollmentService;
use Database\Seeders\RolesAndPermissionsSeeder;
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
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = app(EnrollmentService::class);
        Mail::fake();

        // Create school year
        $this->sy2024 = \App\Models\SchoolYear::firstOrCreate([
            'name' => '2024-2025',
            'start_year' => 2024,
            'end_year' => 2025,
            'start_date' => '2024-06-01',
            'end_date' => '2025-05-31',
            'status' => 'active',
        ]);
    }

    public function test_sends_email_when_enrollment_is_submitted(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'guardian@test.com']);
        $guardian = \App\Models\Guardian::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
        $student = Student::factory()->create();

        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => 'Grade 1',
            'section' => 'A',
        ];

        // Act
        $enrollment = $this->service->createEnrollment($enrollmentData);

        // Assert
        Mail::assertQueued(EnrollmentSubmitted::class, function ($mail) use ($user, $enrollment) {
            return $mail->hasTo($user->email) &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }

    public function test_sends_email_when_enrollment_is_approved(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'guardian@test.com']);
        $guardian = \App\Models\Guardian::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
        $student = Student::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->create());

        // Act
        $this->service->approveEnrollment($enrollment);

        // Assert
        Mail::assertQueued(EnrollmentApproved::class, function ($mail) use ($user, $enrollment) {
            return $mail->hasTo($user->email) &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }

    public function test_sends_email_when_enrollment_is_rejected(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'guardian@test.com']);
        $guardian = \App\Models\Guardian::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
        $student = Student::factory()->create();
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
        Mail::assertQueued(EnrollmentRejected::class, function ($mail) use ($user, $enrollment, $reason) {
            return $mail->hasTo($user->email) &&
                   $mail->enrollment->id === $enrollment->id &&
                   $mail->reason === $reason;
        });
    }

    public function test_sends_multiple_emails_when_bulk_approving_enrollments(): void
    {
        // Arrange
        $users = User::factory()->count(3)->create();
        $guardians = [];
        $enrollments = [];

        foreach ($users as $user) {
            $guardian = \App\Models\Guardian::create([
                'user_id' => $user->id,
                'first_name' => 'Test',
                'last_name' => 'Guardian',
                'contact_number' => '09123456789',
                'address' => '123 Test St',
            ]);
            $guardians[] = $guardian;

            $student = Student::factory()->create();
            $enrollments[] = Enrollment::factory()->create([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'status' => 'pending',
            ]);
        }

        $enrollmentIds = collect($enrollments)->pluck('id')->toArray();
        $this->actingAs(User::factory()->create());

        // Reset mail fake to clear enrollment creation emails
        Mail::fake();

        // Act
        $count = $this->service->bulkApproveEnrollments($enrollmentIds);

        // Assert
        $this->assertEquals(3, $count);
        Mail::assertQueuedCount(3);

        foreach ($enrollments as $index => $enrollment) {
            Mail::assertQueued(EnrollmentApproved::class, function ($mail) use ($users, $enrollment, $index) {
                return $mail->hasTo($users[$index]->email) &&
                       $mail->enrollment->id === $enrollment->id;
            });
        }
    }

    public function test_does_not_send_email_when_guardian_has_no_email(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => '']);
        $guardian = \App\Models\Guardian::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
        $student = Student::factory()->create();

        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $this->sy2024->id,
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
        $user = User::factory()->create(['email' => 'guardian@test.com']);
        $guardian = \App\Models\Guardian::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Guardian',
            'contact_number' => '09123456789',
            'address' => '123 Test St',
        ]);
        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $enrollmentData = [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $this->sy2024->id,
            'grade_level' => 'Grade 5',
            'section' => 'A',
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
