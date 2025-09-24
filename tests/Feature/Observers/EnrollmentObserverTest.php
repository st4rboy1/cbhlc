<?php

namespace Tests\Feature\Observers;

use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentSubmitted;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class EnrollmentObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // Seed roles
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_enrollment_id_is_generated_automatically(): void
    {
        $enrollment = Enrollment::factory()->create(['enrollment_id' => null]);

        $this->assertNotNull($enrollment->enrollment_id);
        $this->assertMatchesRegularExpression('/^ENR-\d{10}$/', $enrollment->enrollment_id);
        $this->assertStringStartsWith('ENR-' . date('Ym'), $enrollment->enrollment_id);
    }

    public function test_default_status_is_set_to_pending(): void
    {
        $enrollment = Enrollment::factory()->create(['status' => null]);

        $this->assertEquals('pending', $enrollment->status->value);
    }

    public function test_fees_are_calculated_when_creating(): void
    {
        $gradeLevelFee = GradeLevelFee::factory()->create([
            'grade_level' => 'Grade 1',
            'school_year' => '2024-2025',
            'tuition_fee' => 20000,
            'miscellaneous_fee' => 5000,
        ]);

        $enrollment = Enrollment::factory()->create([
            'grade_level' => 'Grade 1',
            'school_year' => '2024-2025',
            'tuition_fee' => null,
        ]);

        $this->assertEquals(20000, $enrollment->tuition_fee);
        $this->assertEquals(5000, $enrollment->miscellaneous_fee);
        $this->assertEquals(25000, $enrollment->total_amount);
    }

    public function test_email_is_sent_when_enrollment_is_created(): void
    {
        $guardian = User::factory()->create(['email' => 'guardian@test.com']);
        $student = Student::factory()->create(['guardian_id' => $guardian->id]);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
        ]);

        Mail::assertQueued(EnrollmentSubmitted::class, function ($mail) use ($guardian, $enrollment) {
            return $mail->hasTo($guardian->email) &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }

    public function test_approved_at_is_set_when_status_changes_to_approved(): void
    {
        $this->actingAs(User::factory()->create());

        $enrollment = Enrollment::factory()->create(['status' => 'pending']);

        $enrollment->update(['status' => 'approved']);

        $this->assertNotNull($enrollment->approved_at);
        $this->assertEquals(auth()->id(), $enrollment->approved_by);
    }

    public function test_rejected_at_is_set_when_status_changes_to_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $enrollment = Enrollment::factory()->create(['status' => 'pending']);

        $enrollment->update(['status' => 'rejected']);

        $this->assertNotNull($enrollment->rejected_at);
        $this->assertEquals(auth()->id(), $enrollment->rejected_by);
    }

    public function test_email_is_sent_when_enrollment_is_approved(): void
    {
        $this->actingAs(User::factory()->create());

        $guardian = User::factory()->create(['email' => 'guardian@test.com']);
        $student = Student::factory()->create(['guardian_id' => $guardian->id]);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'status' => 'pending',
        ]);

        Mail::fake(); // Reset mail fake to clear previous queued emails

        $enrollment->update(['status' => 'approved']);

        Mail::assertQueued(EnrollmentApproved::class, function ($mail) use ($guardian, $enrollment) {
            return $mail->hasTo($guardian->email) &&
                   $mail->enrollment->id === $enrollment->id;
        });
    }

    public function test_student_grade_level_is_updated_when_enrollment_is_approved(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create(['grade_level' => 'Grade 1']);
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'status' => 'pending',
            'grade_level' => 'Grade 2',
        ]);

        $enrollment->update(['status' => 'approved']);

        $student->refresh();
        $this->assertEquals('Grade 2', $student->grade_level->value);
    }

    public function test_activity_is_logged_for_enrollment_events(): void
    {
        $this->actingAs(User::factory()->create());

        $enrollment = Enrollment::factory()->create();

        $activity = Activity::where('subject_type', Enrollment::class)
            ->where('subject_id', $enrollment->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Enrollment created for student:', $activity->description);
    }
}