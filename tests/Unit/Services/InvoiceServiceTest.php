<?php

namespace Tests\Unit\Services;

use App\Enums\InvoiceStatus;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->invoiceService = new InvoiceService();
    }

    public function test_create_invoice_with_items(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
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

        $invoice = $this->invoiceService->createInvoice($data);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertNotNull($invoice->invoice_number);
        $this->assertEquals(6000, $invoice->total_amount);
        $this->assertEquals(InvoiceStatus::DRAFT, $invoice->status);
        $this->assertCount(2, $invoice->items);
    }

    public function test_generate_unique_invoice_number(): void
    {
        $enrollment = Enrollment::factory()->create();

        // Create first invoice
        $data1 = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'amount' => 100,
                ],
            ],
        ];

        $invoice1 = $this->invoiceService->createInvoice($data1);

        // Create second invoice
        $data2 = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test Item 2',
                    'quantity' => 1,
                    'unit_price' => 200,
                    'amount' => 200,
                ],
            ],
        ];

        $invoice2 = $this->invoiceService->createInvoice($data2);

        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice1->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice2->invoice_number);
    }

    public function test_recalculate_totals(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'amount' => 100,
                ],
            ],
        ];

        $invoice = $this->invoiceService->createInvoice($data);

        // Add more items manually
        $invoice->items()->create([
            'description' => 'Additional Item',
            'quantity' => 2,
            'unit_price' => 50,
            'amount' => 100,
        ]);

        // Recalculate totals
        $invoice = $this->invoiceService->recalculateTotals($invoice);

        $this->assertEquals(200, $invoice->total_amount);
    }

    public function test_invoice_number_format(): void
    {
        $enrollment = Enrollment::factory()->create();

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'amount' => 100,
                ],
            ],
        ];

        $invoice = $this->invoiceService->createInvoice($data);

        $year = date('Y');
        $month = date('m');
        $pattern = "/^INV-{$year}{$month}-\d{4}$/";

        $this->assertMatchesRegularExpression($pattern, $invoice->invoice_number);
    }
}