<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreReceiptRequest;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreReceiptRequestTest extends TestCase
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

        $request = new StoreReceiptRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_true_for_administrator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        $request = new StoreReceiptRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_true_for_registrar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $request = new StoreReceiptRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_other_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $request = new StoreReceiptRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreReceiptRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('payment_id', $rules);
        $this->assertArrayHasKey('invoice_id', $rules);
        $this->assertArrayHasKey('receipt_date', $rules);
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
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
        ]);

        $data = [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'receipt_date' => '2025-01-15',
            'amount' => 5000.00,
            'payment_method' => 'cash',
            'notes' => 'Payment received in full',
        ];

        $request = new StoreReceiptRequest;
        $validator = validator($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_invalid_payment_id(): void
    {
        $data = [
            'payment_id' => 99999,
            'receipt_date' => '2025-01-15',
            'amount' => 5000.00,
            'payment_method' => 'cash',
        ];

        $request = new StoreReceiptRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('payment_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_negative_amount(): void
    {
        $data = [
            'receipt_date' => '2025-01-15',
            'amount' => -100.00,
            'payment_method' => 'cash',
        ];

        $request = new StoreReceiptRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $data = [];

        $request = new StoreReceiptRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('receipt_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
        $this->assertArrayHasKey('payment_method', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreReceiptRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('payment_id.exists', $messages);
        $this->assertArrayHasKey('invoice_id.exists', $messages);
        $this->assertArrayHasKey('receipt_date.required', $messages);
        $this->assertArrayHasKey('amount.required', $messages);
        $this->assertArrayHasKey('amount.min', $messages);
        $this->assertArrayHasKey('payment_method.required', $messages);
    }
}
