<?php

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Notifications\InvoiceCreatedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('Guardian Invoice Notification', function () {

    test('guardian can access invoice from notification link', function () {
        Notification::fake();

        // Create guardian user
        $user = User::factory()->create([
            'email' => 'guardian@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('guardian');

        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create student and enrollment
        $student = Student::factory()->create();
        $guardian->children()->attach($student->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'status' => 'enrolled',
            'payment_plan' => 'monthly',
        ]);

        // Create invoice
        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'invoice_number' => 'INV-2025-0001',
            'total_amount' => 50000,
            'status' => 'sent',
        ]);

        // Send notification
        $user->notify(new InvoiceCreatedNotification($invoice));

        // Assert notification was sent
        Notification::assertSentTo($user, InvoiceCreatedNotification::class);

        // Login as guardian and access invoice
        $this->actingAs($user);

        $response = $this->get(route('guardian.invoices.show', $invoice));

        // Should successfully load the invoice page (not 404)
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('shared/invoice')
            ->has('enrollment')
            ->where('invoiceNumber', $invoice->invoice_number)
        );
    })->group('guardian', 'invoice', 'notification', 'critical');

    test('guardian cannot access invoice for other guardians student', function () {
        // Create first guardian with student
        $user1 = User::factory()->create();
        $user1->assignRole('guardian');
        $guardian1 = Guardian::factory()->create(['user_id' => $user1->id]);
        $student1 = Student::factory()->create();
        $guardian1->children()->attach($student1->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment1 = Enrollment::factory()->create([
            'student_id' => $student1->id,
            'guardian_id' => $guardian1->id,
            'school_year_id' => $schoolYear->id,
            'payment_plan' => 'monthly',
        ]);

        $invoice1 = Invoice::factory()->create([
            'enrollment_id' => $enrollment1->id,
            'invoice_number' => 'INV-2025-0001',
            'status' => 'sent',
        ]);

        // Create second guardian with different student
        $user2 = User::factory()->create();
        $user2->assignRole('guardian');
        $guardian2 = Guardian::factory()->create(['user_id' => $user2->id]);
        $student2 = Student::factory()->create();
        $guardian2->children()->attach($student2->id, ['is_primary_contact' => true]);

        // Login as second guardian and try to access first guardian's invoice
        $this->actingAs($user2);

        $response = $this->get(route('guardian.invoices.show', $invoice1));

        // Should return 404 for security
        $response->assertStatus(404);
    })->group('guardian', 'invoice', 'authorization', 'critical');

    test('invoice notification contains correct data', function () {
        Notification::fake();

        $user = User::factory()->create();
        $user->assignRole('guardian');
        $guardian = Guardian::factory()->create(['user_id' => $user->id]);
        $student = Student::factory()->create();
        $guardian->children()->attach($student->id, ['is_primary_contact' => true]);

        $schoolYear = SchoolYear::factory()->create(['status' => 'active']);

        $enrollment = Enrollment::factory()->create([
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'school_year_id' => $schoolYear->id,
            'payment_plan' => 'monthly',
        ]);

        $invoice = Invoice::factory()->create([
            'enrollment_id' => $enrollment->id,
            'invoice_number' => 'INV-2025-0002',
            'total_amount' => 75000,
            'status' => 'sent',
        ]);

        // Send notification
        $user->notify(new InvoiceCreatedNotification($invoice));

        // Assert notification data
        Notification::assertSentTo(
            $user,
            InvoiceCreatedNotification::class,
            function ($notification) use ($invoice) {
                return $notification->invoice->id === $invoice->id &&
                       $notification->invoice->invoice_number === $invoice->invoice_number &&
                       $notification->invoice->total_amount == $invoice->total_amount &&
                       $notification->invoice->status->value === $invoice->status->value;
            }
        );
    })->group('guardian', 'invoice', 'notification');
});
