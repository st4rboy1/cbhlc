<?php

use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentVerifiedNotification;
use App\Notifications\InvoiceCreatedNotification;
use App\Notifications\NewUserRegisteredNotification;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('#528: document notification redirects properly instead of returning JSON', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student linked to this guardian
    $student = Student::factory()->create();
    $guardianModel->children()->attach($student->id, [
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    // Create a document for the student
    $document = Document::factory()->create([
        'student_id' => $student->id,
        'verification_status' => \App\Enums\VerificationStatus::VERIFIED,
    ]);

    // Create a document verified notification
    $notification = $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => DocumentVerifiedNotification::class,
        'data' => [
            'message' => 'Document Verified',
            'document_id' => $document->id,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
        ],
        'read_at' => null,
    ]);

    // Click on the notification (mark as read)
    $response = actingAs($user)
        ->post("/notifications/{$notification->id}/mark-as-read");

    // Should redirect to student documents page (not return JSON)
    $response->assertRedirect(route('guardian.students.documents.index', ['student' => $student->id]));

    // Verify notification was marked as read
    expect($notification->fresh()->read_at)->not->toBeNull();
})->group('browser', 'bug', 'issue-528');

test('#523: payment notification redirects to invoice page', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student and enrollment
    $student = Student::factory()->create();
    $guardianModel->children()->attach($student->id, [
        'relationship_type' => 'father',
        'is_primary_contact' => true,
    ]);

    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardianModel->id,
    ]);

    // Create an invoice and payment
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);

    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    // Create a payment received notification
    $notification = $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => PaymentReceivedNotification::class,
        'data' => [
            'message' => 'Payment Received',
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'amount' => $payment->amount,
        ],
        'read_at' => null,
    ]);

    // Click on the notification
    $response = actingAs($user)
        ->post("/notifications/{$notification->id}/mark-as-read");

    // Should redirect to invoice show page
    $response->assertRedirect(route('guardian.invoices.show', ['invoice' => $invoice->id]));

    // Verify notification was marked as read
    expect($notification->fresh()->read_at)->not->toBeNull();
})->group('browser', 'bug', 'issue-523');

test('#520: invoice notification redirects to invoice page', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');
    $guardianModel = Guardian::factory()->create(['user_id' => $user->id]);

    // Create a student and enrollment
    $student = Student::factory()->create();
    $guardianModel->children()->attach($student->id, [
        'relationship_type' => 'mother',
        'is_primary_contact' => true,
    ]);

    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'guardian_id' => $guardianModel->id,
    ]);

    // Create an invoice
    $invoice = Invoice::factory()->create([
        'enrollment_id' => $enrollment->id,
    ]);

    // Create an invoice created notification
    $notification = $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => InvoiceCreatedNotification::class,
        'data' => [
            'message' => 'New Invoice Created',
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $invoice->total_amount,
        ],
        'read_at' => null,
    ]);

    // Click on the notification
    $response = actingAs($user)
        ->post("/notifications/{$notification->id}/mark-as-read");

    // Should redirect to invoice show page
    $response->assertRedirect(route('guardian.invoices.show', ['invoice' => $invoice->id]));

    // Verify notification was marked as read
    expect($notification->fresh()->read_at)->not->toBeNull();
})->group('browser', 'bug', 'issue-520');

test('#525: user registration notification redirects to user page for admin', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a super admin user
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    // Create a new user that just registered
    $newUser = User::factory()->create();
    $newUser->assignRole('guardian');

    // Create a new user registered notification
    $notification = $admin->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => NewUserRegisteredNotification::class,
        'data' => [
            'message' => 'New User Registered',
            'user_id' => $newUser->id,
            'user_name' => $newUser->name,
            'user_email' => $newUser->email,
        ],
        'read_at' => null,
    ]);

    // Click on the notification
    $response = actingAs($admin)
        ->post("/notifications/{$notification->id}/mark-as-read");

    // Should redirect to user show page (not 403 error)
    $response->assertRedirect(route('super-admin.users.show', ['user' => $newUser->id]));

    // Verify notification was marked as read
    expect($notification->fresh()->read_at)->not->toBeNull();
})->group('browser', 'bug', 'issue-525');

test('notification fallback redirects to dashboard when invoice_id missing', function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    // Create a guardian user
    $user = User::factory()->create();
    $user->assignRole('guardian');
    Guardian::factory()->create(['user_id' => $user->id]);

    // Create an invoice notification WITHOUT invoice_id
    $notification = $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => InvoiceCreatedNotification::class,
        'data' => [
            'message' => 'New Invoice Created',
            // invoice_id is missing
        ],
        'read_at' => null,
    ]);

    // Click on the notification
    $response = actingAs($user)
        ->post("/notifications/{$notification->id}/mark-as-read");

    // Should redirect to invoices index as fallback
    $response->assertRedirect(route('guardian.invoices.index'));

    // Verify notification was marked as read
    expect($notification->fresh()->read_at)->not->toBeNull();
})->group('browser', 'bug', 'issue-520');
