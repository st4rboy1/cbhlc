<?php

namespace Tests\Unit\Http\Requests\SuperAdmin;

use App\Http\Requests\SuperAdmin\StoreInvoiceRequest;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreInvoiceRequestTest extends TestCase
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

        $request = new StoreInvoiceRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_true_for_registrar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $request = new StoreInvoiceRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_other_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guardian');

        $request = new StoreInvoiceRequest;
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreInvoiceRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('enrollment_id', $rules);
        $this->assertArrayHasKey('invoice_date', $rules);
        $this->assertArrayHasKey('items', $rules);
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $student = Student::factory()->create();
        $schoolYear = SchoolYear::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'school_year_id' => $schoolYear->id,
        ]);

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => '2025-01-15',
            'due_date' => '2025-02-15',
            'items' => [
                [
                    'description' => 'Tuition Fee',
                    'quantity' => 1,
                    'unit_price' => 15000.00,
                    'amount' => 15000.00,
                ],
            ],
        ];

        $request = new StoreInvoiceRequest;
        $validator = validator($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_due_date_before_invoice_date(): void
    {
        $student = Student::factory()->create();
        $schoolYear = SchoolYear::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'school_year_id' => $schoolYear->id,
        ]);

        $data = [
            'enrollment_id' => $enrollment->id,
            'invoice_date' => '2025-02-15',
            'due_date' => '2025-01-15',
            'items' => [
                [
                    'description' => 'Tuition Fee',
                    'quantity' => 1,
                    'unit_price' => 15000.00,
                    'amount' => 15000.00,
                ],
            ],
        ];

        $request = new StoreInvoiceRequest;
        $validator = validator($data, $request->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_custom_messages(): void
    {
        $request = new StoreInvoiceRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('items.required', $messages);
        $this->assertArrayHasKey('due_date.after', $messages);
    }
}
