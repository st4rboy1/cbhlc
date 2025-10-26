<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StorePaymentRequest;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_authorize_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $request = new StorePaymentRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StorePaymentRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('invoice_id', $rules);
        $this->assertArrayHasKey('payment_date', $rules);
        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('payment_method', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $student = Student::factory()->create();
        $schoolYear = SchoolYear::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'school_year_id' => $schoolYear->id,
        ]);
        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
        ]);

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => '2025-01-15',
            'amount' => 5000.00,
            'payment_method' => 'cash',
        ];

        $request = new StorePaymentRequest;
        $validator = validator($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_invalid_invoice_id(): void
    {
        $data = [
            'invoice_id' => 99999,
            'payment_date' => '2025-01-15',
            'amount' => 5000.00,
            'payment_method' => 'cash',
        ];

        $request = new StorePaymentRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_validation_fails_with_invalid_payment_method(): void
    {
        $student = Student::factory()->create();
        $schoolYear = SchoolYear::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'school_year_id' => $schoolYear->id,
        ]);
        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
        ]);

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => '2025-01-15',
            'amount' => 5000.00,
            'payment_method' => 'invalid_method',
        ];

        $request = new StorePaymentRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
    }
}
