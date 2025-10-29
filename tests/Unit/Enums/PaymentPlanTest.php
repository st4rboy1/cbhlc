<?php

namespace Tests\Unit\Enums;

use App\Enums\PaymentPlan;
use PHPUnit\Framework\TestCase;

class PaymentPlanTest extends TestCase
{
    public function test_values_returns_all_payment_plan_values(): void
    {
        $values = PaymentPlan::values();

        $this->assertCount(4, $values);
        $this->assertContains('annual', $values);
        $this->assertContains('semestral', $values);
        $this->assertContains('quarterly', $values);
        $this->assertContains('monthly', $values);
    }

    public function test_label_returns_correct_labels(): void
    {
        $this->assertEquals('Annual', PaymentPlan::ANNUAL->label());
        $this->assertEquals('Semestral', PaymentPlan::SEMESTRAL->label());
        $this->assertEquals('Quarterly', PaymentPlan::QUARTERLY->label());
        $this->assertEquals('Monthly', PaymentPlan::MONTHLY->label());
    }

    public function test_installments_returns_correct_counts(): void
    {
        $this->assertEquals(1, PaymentPlan::ANNUAL->installments());
        $this->assertEquals(2, PaymentPlan::SEMESTRAL->installments());
        $this->assertEquals(4, PaymentPlan::QUARTERLY->installments());
        $this->assertEquals(10, PaymentPlan::MONTHLY->installments());
    }

    public function test_description_returns_valid_descriptions(): void
    {
        $this->assertNotEmpty(PaymentPlan::ANNUAL->description());
        $this->assertNotEmpty(PaymentPlan::SEMESTRAL->description());
        $this->assertNotEmpty(PaymentPlan::QUARTERLY->description());
        $this->assertNotEmpty(PaymentPlan::MONTHLY->description());
    }
}
