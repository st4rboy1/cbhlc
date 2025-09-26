<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePaymentRequestTest extends TestCase
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

        $request = new UpdatePaymentRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new UpdatePaymentRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('invoice_id', $rules);
        $this->assertArrayHasKey('payment_date', $rules);
        $this->assertArrayHasKey('amount', $rules);
        $this->assertArrayHasKey('payment_method', $rules);
        $this->assertArrayHasKey('reference_number', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('notes', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $invoice = Invoice::factory()->create();

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 5000,
            'payment_method' => 'cash',
            'reference_number' => 'REF-123456',
            'status' => 'completed',
            'notes' => 'Payment received in cash',
        ];

        $request = new UpdatePaymentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_optional_fields(): void
    {
        $invoice = Invoice::factory()->create();

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 1000,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            // No reference_number and notes
        ];

        $request = new UpdatePaymentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_amount(): void
    {
        $invoice = Invoice::factory()->create();

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 0, // Invalid amount (minimum is 0.01)
            'payment_method' => 'cash',
            'status' => 'completed',
        ];

        $request = new UpdatePaymentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_payment_method(): void
    {
        $invoice = Invoice::factory()->create();

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 1000,
            'payment_method' => 'invalid_method',
            'status' => 'completed',
        ];

        $request = new UpdatePaymentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('payment_method', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_status(): void
    {
        $invoice = Invoice::factory()->create();

        $data = [
            'invoice_id' => $invoice->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 1000,
            'payment_method' => 'cash',
            'status' => 'invalid_status',
        ];

        $request = new UpdatePaymentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_non_existent_invoice(): void
    {
        $data = [
            'invoice_id' => 999999, // Non-existent invoice
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 1000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ];

        $request = new UpdatePaymentRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('invoice_id', $validator->errors()->toArray());
    }
}
