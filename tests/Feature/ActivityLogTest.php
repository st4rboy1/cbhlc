<?php

use App\Listeners\LogAuthenticationActivity;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\GradeLevelFee;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create test user
    $this->user = User::factory()->create();
    $this->user->assignRole('administrator');
});

// ========================================
// MODEL LOGS ACTIVITY TESTS
// ========================================

test('user model logs creation', function () {
    $newUser = User::factory()->create(['name' => 'Test User']);

    expect(Activity::where('subject_type', User::class)
        ->where('subject_id', $newUser->id)
        ->where('description', 'User account created')
        ->exists())->toBeTrue();
});

test('user model logs updates', function () {
    actingAs($this->user);

    $this->user->update(['name' => 'Updated Name']);

    expect(Activity::where('subject_type', User::class)
        ->where('subject_id', $this->user->id)
        ->where('description', 'User account updated')
        ->exists())->toBeTrue();
});

test('student model logs creation', function () {
    actingAs($this->user);

    $student = Student::factory()->create();

    expect(Activity::where('subject_type', Student::class)
        ->where('subject_id', $student->id)
        ->where('description', 'Student record created')
        ->exists())->toBeTrue();
});

test('student model logs updates', function () {
    actingAs($this->user);

    $student = Student::factory()->create();
    $student->update(['first_name' => 'Updated']);

    expect(Activity::where('subject_type', Student::class)
        ->where('subject_id', $student->id)
        ->where('description', 'Student record updated')
        ->exists())->toBeTrue();
});

test('guardian model logs creation', function () {
    actingAs($this->user);

    $guardian = Guardian::factory()->create();

    expect(Activity::where('subject_type', Guardian::class)
        ->where('subject_id', $guardian->id)
        ->where('description', 'Guardian record created')
        ->exists())->toBeTrue();
});

test('guardian model logs updates', function () {
    actingAs($this->user);

    $guardian = Guardian::factory()->create();
    $guardian->update(['first_name' => 'Updated']);

    expect(Activity::where('subject_type', Guardian::class)
        ->where('subject_id', $guardian->id)
        ->where('description', 'Guardian record updated')
        ->exists())->toBeTrue();
});

test('enrollment model logs creation', function () {
    actingAs($this->user);

    $enrollment = Enrollment::factory()->create();

    expect(Activity::where('subject_type', Enrollment::class)
        ->where('subject_id', $enrollment->id)
        ->where('description', 'Enrollment application created')
        ->exists())->toBeTrue();
});

test('enrollment model logs updates', function () {
    actingAs($this->user);

    $enrollment = Enrollment::factory()->create();
    $enrollment->update(['remarks' => 'Test remarks']);

    expect(Activity::where('subject_type', Enrollment::class)
        ->where('subject_id', $enrollment->id)
        ->where('description', 'Enrollment application updated')
        ->exists())->toBeTrue();
});

test('grade level fee model logs creation', function () {
    actingAs($this->user);

    $fee = GradeLevelFee::factory()->create();

    expect(Activity::where('subject_type', GradeLevelFee::class)
        ->where('subject_id', $fee->id)
        ->where('description', 'Grade level fee created')
        ->exists())->toBeTrue();
});

test('grade level fee model logs updates', function () {
    actingAs($this->user);

    $fee = GradeLevelFee::factory()->create();
    $fee->update(['tuition_fee_cents' => 100000]);

    expect(Activity::where('subject_type', GradeLevelFee::class)
        ->where('subject_id', $fee->id)
        ->where('description', 'Grade level fee updated')
        ->exists())->toBeTrue();
});

test('invoice model logs creation', function () {
    actingAs($this->user);

    $invoice = Invoice::factory()->create();

    expect(Activity::where('subject_type', Invoice::class)
        ->where('subject_id', $invoice->id)
        ->where('description', 'Invoice created')
        ->exists())->toBeTrue();
});

test('invoice model logs updates', function () {
    actingAs($this->user);

    $invoice = Invoice::factory()->create();
    $invoice->update(['notes' => 'Test notes']);

    expect(Activity::where('subject_type', Invoice::class)
        ->where('subject_id', $invoice->id)
        ->where('description', 'Invoice updated')
        ->exists())->toBeTrue();
});

test('payment model logs creation', function () {
    actingAs($this->user);

    $payment = Payment::factory()->create();

    expect(Activity::where('subject_type', Payment::class)
        ->where('subject_id', $payment->id)
        ->where('description', 'Payment recorded')
        ->exists())->toBeTrue();
});

test('payment model logs updates', function () {
    actingAs($this->user);

    $payment = Payment::factory()->create();
    $payment->update(['notes' => 'Test notes']);

    expect(Activity::where('subject_type', Payment::class)
        ->where('subject_id', $payment->id)
        ->where('description', 'Payment updated')
        ->exists())->toBeTrue();
});

test('document model logs creation', function () {
    actingAs($this->user);

    $document = Document::factory()->create();

    expect(Activity::where('subject_type', Document::class)
        ->where('subject_id', $document->id)
        ->where('description', 'Document uploaded')
        ->exists())->toBeTrue();
});

test('document model logs updates', function () {
    actingAs($this->user);

    $document = Document::factory()->create();
    $document->update(['rejection_reason' => 'Test reason']);

    expect(Activity::where('subject_type', Document::class)
        ->where('subject_id', $document->id)
        ->where('description', 'Document updated')
        ->exists())->toBeTrue();
});

// ========================================
// AUTHENTICATION EVENT TESTS
// ========================================

test('login event logs activity', function () {
    $user = User::factory()->create();

    $event = new Login('web', $user, false);
    $listener = new LogAuthenticationActivity;
    $listener->handleLogin($event);

    $activity = Activity::where('description', 'User logged in')
        ->where('causer_id', $user->id)
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('guard');
    expect($activity->properties)->toHaveKey('ip_address');
    expect($activity->properties)->toHaveKey('user_agent');
});

test('logout event logs activity', function () {
    $user = User::factory()->create();

    $event = new Logout('web', $user);
    $listener = new LogAuthenticationActivity;
    $listener->handleLogout($event);

    $activity = Activity::where('description', 'User logged out')
        ->where('causer_id', $user->id)
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('guard');
    expect($activity->properties)->toHaveKey('ip_address');
});

test('failed login attempt logs activity', function () {
    $credentials = ['email' => 'test@example.com', 'password' => 'wrongpassword'];

    $event = new Failed('web', null, $credentials);
    $listener = new LogAuthenticationActivity;
    $listener->handleFailed($event);

    $activity = Activity::where('description', 'Failed login attempt')->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('email');
    expect($activity->properties['email'])->toBe('test@example.com');
    expect($activity->properties)->toHaveKey('ip_address');
    expect($activity->properties)->toHaveKey('user_agent');
    expect($activity->properties)->toHaveKey('guard');
});

// ========================================
// ACTIVITY LOG PROPERTIES TESTS
// ========================================

test('activity log contains causer information', function () {
    actingAs($this->user);

    $student = Student::factory()->create();

    $activity = Activity::where('subject_type', Student::class)
        ->where('subject_id', $student->id)
        ->first();

    expect($activity->causer_id)->toBe($this->user->id);
    expect($activity->causer_type)->toBe(User::class);
});

test('activity log only logs dirty attributes', function () {
    actingAs($this->user);

    $student = Student::factory()->create(['first_name' => 'John']);

    // Clear existing activities
    Activity::query()->delete();

    // Update without changes
    $student->save();

    // Should not log anything because nothing changed
    expect(Activity::where('subject_type', Student::class)
        ->where('subject_id', $student->id)
        ->exists())->toBeFalse();

    // Update with actual changes
    $student->update(['first_name' => 'Jane']);

    // Should log this change
    $activity = Activity::where('subject_type', Student::class)
        ->where('subject_id', $student->id)
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties['attributes']['first_name'])->toBe('Jane');
});

test('activity log stores changed attributes', function () {
    actingAs($this->user);

    $student = Student::factory()->create(['first_name' => 'John']);
    $student->update(['first_name' => 'Jane']);

    $activity = Activity::where('subject_type', Student::class)
        ->where('subject_id', $student->id)
        ->where('description', 'Student record updated')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('attributes');
    expect($activity->properties)->toHaveKey('old');
    expect($activity->properties['attributes']['first_name'])->toBe('Jane');
    expect($activity->properties['old']['first_name'])->toBe('John');
});

// ========================================
// ACTIVITY LOG DELETION TESTS
// ========================================

test('activity log records deletion', function () {
    actingAs($this->user);

    $student = Student::factory()->create();
    $studentId = $student->id;

    $student->delete();

    expect(Activity::where('subject_type', Student::class)
        ->where('subject_id', $studentId)
        ->where('description', 'Student record deleted')
        ->exists())->toBeTrue();
});

test('document soft delete logs activity', function () {
    actingAs($this->user);

    $document = Document::factory()->create();
    $documentId = $document->id;

    $document->delete(); // Soft delete

    expect(Activity::where('subject_type', Document::class)
        ->where('subject_id', $documentId)
        ->where('description', 'Document deleted')
        ->exists())->toBeTrue();
});
