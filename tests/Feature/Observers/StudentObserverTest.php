<?php

namespace Tests\Feature\Observers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class StudentObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_id_is_generated_automatically_when_creating(): void
    {
        $student = Student::factory()->create(['student_id' => null]);

        $this->assertNotNull($student->student_id);
        $this->assertMatchesRegularExpression('/^\d{8}$/', $student->student_id);
        $this->assertStringStartsWith(date('Y'), $student->student_id);
    }

    public function test_student_id_is_uppercase_when_provided(): void
    {
        $student = Student::factory()->create(['student_id' => 'test123']);

        $this->assertEquals('TEST123', $student->student_id);
    }

    public function test_student_id_generation_increments_correctly(): void
    {
        $year = date('Y');

        // Create first student
        $student1 = Student::factory()->create(['student_id' => null]);
        $this->assertEquals($year . '0001', $student1->student_id);

        // Create second student
        $student2 = Student::factory()->create(['student_id' => null]);
        $this->assertEquals($year . '0002', $student2->student_id);

        // Create third student
        $student3 = Student::factory()->create(['student_id' => null]);
        $this->assertEquals($year . '0003', $student3->student_id);
    }

    public function test_activity_is_logged_when_student_is_created(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('Student created: ' . $student->full_name, $activity->description);
        $this->assertEquals(Student::class, $activity->subject_type);
        $this->assertEquals($student->id, $activity->subject_id);
    }

    public function test_activity_is_logged_when_student_is_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();
        Activity::truncate(); // Clear previous activities

        $student->update(['first_name' => 'UpdatedName']);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('Student updated: ' . $student->full_name, $activity->description);
        $this->assertArrayHasKey('changes', $activity->properties->toArray());
    }

    public function test_activity_is_logged_when_student_is_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();
        $fullName = $student->full_name;
        Activity::truncate(); // Clear previous activities

        $student->delete();

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('Student deleted: ' . $fullName, $activity->description);
    }

    public function test_no_activity_logged_when_no_significant_changes(): void
    {
        $this->actingAs(User::factory()->create());

        $student = Student::factory()->create();
        Activity::truncate(); // Clear previous activities

        // Update a non-significant field
        $student->update(['email' => 'newemail@example.com']);

        $activity = Activity::latest()->first();
        $this->assertNull($activity);
    }
}