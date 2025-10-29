<?php

namespace Tests\Unit\Enums;

use App\Enums\EnrollmentType;
use PHPUnit\Framework\TestCase;

class EnrollmentTypeTest extends TestCase
{
    public function test_values_returns_all_enrollment_type_values(): void
    {
        $values = EnrollmentType::values();

        $this->assertCount(4, $values);
        $this->assertContains('new', $values);
        $this->assertContains('continuing', $values);
        $this->assertContains('returnee', $values);
        $this->assertContains('transferee', $values);
    }

    public function test_label_returns_correct_labels(): void
    {
        $this->assertEquals('New Student', EnrollmentType::NEW->label());
        $this->assertEquals('Continuing Student', EnrollmentType::CONTINUING->label());
        $this->assertEquals('Returnee', EnrollmentType::RETURNEE->label());
        $this->assertEquals('Transferee', EnrollmentType::TRANSFEREE->label());
    }

    public function test_description_returns_valid_descriptions(): void
    {
        $this->assertNotEmpty(EnrollmentType::NEW->description());
        $this->assertNotEmpty(EnrollmentType::CONTINUING->description());
        $this->assertNotEmpty(EnrollmentType::RETURNEE->description());
        $this->assertNotEmpty(EnrollmentType::TRANSFEREE->description());
    }

    public function test_requires_previous_school_only_for_transferee(): void
    {
        $this->assertFalse(EnrollmentType::NEW->requiresPreviousSchool());
        $this->assertFalse(EnrollmentType::CONTINUING->requiresPreviousSchool());
        $this->assertFalse(EnrollmentType::RETURNEE->requiresPreviousSchool());
        $this->assertTrue(EnrollmentType::TRANSFEREE->requiresPreviousSchool());
    }
}
