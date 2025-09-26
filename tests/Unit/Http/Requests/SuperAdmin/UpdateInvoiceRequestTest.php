<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\UpdateInvoiceRequest;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateInvoiceRequestTest extends TestCase
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

        $request = new UpdateInvoiceRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new UpdateInvoiceRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('enrollment_id', $rules);
        $this->assertArrayHasKey('invoice_date', $rules);
        $this->assertArrayHasKey('due_date', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('items', $rules);
        $this->assertArrayHasKey('items.*.description', $rules);
        $this->assertArrayHasKey('items.*.quantity', $rules);
        $this->assertArrayHasKey('items.*.unit_price', $rules);
        $this->assertArrayHasKey('items.*.amount', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft',
            'items' => [
                [
                    'description' => 'Tuition Fee',
                    'quantity' => 1,
                    'unit_price' => 5000,
                    'amount' => 5000,
                ],
                [
                    'description' => 'Miscellaneous Fee',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'amount' => 1000,
                ],
            ],
        ];

        $request = new UpdateInvoiceRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_existing_invoice_items(): void
    {
        $enrollment = Enrollment::factory()->create();
        $invoice = Invoice::factory()->create(['enrollment_id' => $enrollment->id]);
        $invoiceItem = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'sent',
            'items' => [
                [
                    'id' => $invoiceItem->id,
                    'description' => 'Updated Item',
                    'quantity' => 2,
                    'unit_price' => 2500,
                    'amount' => 5000,
                ],
            ],
        ];

        $request = new UpdateInvoiceRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_due_date_before_invoice_date(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->subDays(5)->format('Y-m-d'), // Before invoice date
            'status' => 'draft',
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'amount' => 100,
                ],
            ],
        ];

        $request = new UpdateInvoiceRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('due_date', $validator->errors()->toArray());
    }

    public function test_validation_fails_without_items(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'draft',
            'items' => [], // Empty items
        ];

        $request = new UpdateInvoiceRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('items', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_status(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'invalid_status',
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'amount' => 100,
                ],
            ],
        ];

        $request = new UpdateInvoiceRequest;
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_custom_messages(): void
    {
        $request = new UpdateInvoiceRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('items.required', $messages);
        $this->assertArrayHasKey('items.min', $messages);
        $this->assertArrayHasKey('due_date.after', $messages);
        $this->assertEquals('At least one invoice item is required.', $messages['items.required']);
    }
}
