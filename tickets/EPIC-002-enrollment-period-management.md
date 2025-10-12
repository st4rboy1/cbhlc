# Ticket #002: Enrollment Period Management

## Priority: High (Should Have)

## Related SRS Requirements

- **Section 8.2.2:** ENROLLMENT_PERIOD entity (Supporting Entities in ERD)
- **FR-4.6:** System shall support enrollment period constraints
- **Section 3.4:** Enrollment Processing Module

## Current Status

❌ **NOT IMPLEMENTED**

No enrollment period management exists:

- No `enrollment_periods` migration file
- No `EnrollmentPeriod` model
- No period management UI
- School year is stored as string in enrollments without validation

## Required Implementation

### 1. Database Layer

Create migration: `create_enrollment_periods_table.php`

```php
Schema::create('enrollment_periods', function (Blueprint $table) {
    $table->id();
    $table->string('school_year', 9)->unique(); // e.g., "2025-2026"
    $table->date('start_date');
    $table->date('end_date');
    $table->date('early_registration_deadline')->nullable();
    $table->date('regular_registration_deadline');
    $table->date('late_registration_deadline')->nullable();
    $table->enum('status', ['upcoming', 'active', 'closed'])->default('upcoming');
    $table->text('description')->nullable();
    $table->boolean('allow_new_students')->default(true);
    $table->boolean('allow_returning_students')->default(true);
    $table->timestamps();
});
```

### 2. Model Layer

Create `app/Models/EnrollmentPeriod.php`:

**Relationships:**

- `enrollments()` - HasMany relationship

**Scopes:**

- `active()` - Get currently active period
- `upcoming()` - Get upcoming periods
- `closed()` - Get closed periods

**Methods:**

- `isActive()` - Check if period is currently active
- `isOpen()` - Check if enrollments can be submitted
- `getDaysRemaining()` - Calculate days until deadline
- `activate()` - Activate period
- `close()` - Close period

**Validation:**

- Ensure only one active period at a time
- Validate date ranges (start < end)
- Validate deadlines are within period dates

### 3. Backend Layer

**Controllers:**

- `SuperAdmin/EnrollmentPeriodController.php` - Full CRUD
- `Registrar/EnrollmentPeriodController.php` - View and update status
- `Public/EnrollmentPeriodController.php` - View active periods

**Routes:**

```php
// Super Admin routes
Route::resource('super-admin.enrollment-periods', SuperAdminEnrollmentPeriodController::class);
Route::post('/super-admin/enrollment-periods/{period}/activate', 'activate');
Route::post('/super-admin/enrollment-periods/{period}/close', 'close');

// Registrar routes
Route::get('/registrar/enrollment-periods', [RegistrarEnrollmentPeriodController::class, 'index']);
Route::post('/registrar/enrollment-periods/{period}/close', 'close');

// Public API
Route::get('/api/enrollment-periods/active', [PublicController::class, 'activeEnrollmentPeriod']);
```

**Business Logic:**

- Prevent enrollment submissions outside active periods
- Automatic period status updates based on dates
- Handle period transitions (upcoming → active → closed)
- Warning notifications for approaching deadlines

### 4. Frontend Layer

**Super Admin Pages:**

- `/resources/js/pages/super-admin/enrollment-periods/index.tsx` - List all periods
- `/resources/js/pages/super-admin/enrollment-periods/create.tsx` - Create new period
- `/resources/js/pages/super-admin/enrollment-periods/edit.tsx` - Edit period
- `/resources/js/pages/super-admin/enrollment-periods/show.tsx` - Period details

**Registrar Pages:**

- `/resources/js/pages/registrar/enrollment-periods/index.tsx` - View periods
- Dashboard widget showing current period info

**Public Pages:**

- Display active enrollment period on landing page
- Show deadline countdown
- Display period status on application page

**Components:**

- `EnrollmentPeriodCard` - Display period information
- `EnrollmentPeriodTimeline` - Visual timeline of deadlines
- `EnrollmentPeriodStatusBadge` - Status indicator
- `DeadlineCountdown` - Countdown timer component

### 5. Enrollment Validation

Update `GuardianEnrollmentController`:

- Validate enrollment submissions against active period
- Display appropriate error messages when period is closed
- Show deadline information in enrollment forms

Update `Enrollment` model:

- Add relationship to `EnrollmentPeriod`
- Validate school_year against existing periods

### 6. Scheduled Jobs

Create `app/Console/Commands/UpdateEnrollmentPeriodStatus.php`:

- Automatically activate periods when start_date is reached
- Automatically close periods when end_date is reached
- Send notifications to admins about period transitions

Schedule in `app/Console/Kernel.php`:

```php
$schedule->command('enrollment-periods:update-status')->daily();
```

## Acceptance Criteria

✅ Super Admin can create, edit, and delete enrollment periods
✅ Only one enrollment period can be active at a time
✅ System prevents enrollments outside active periods
✅ Registrar can view all periods and close current period
✅ Public users can see active enrollment period information
✅ Period status updates automatically based on dates
✅ Dashboard displays current period and deadline countdown
✅ Clear error messages when attempting to enroll outside period

## Testing Requirements

- Unit tests for EnrollmentPeriod model methods
- Feature tests for CRUD operations
- Business logic tests for period transitions
- Validation tests for enrollment submissions
- Scheduled job tests for automatic status updates

## Estimated Effort

**Medium Priority:** 2-3 days

## Dependencies

- Requires Super Admin role access
- May need notification system for deadline reminders
- Requires updates to enrollment submission logic

## Notes

- Consider adding early bird discount configuration per period
- Add grace period configuration for late enrollments
- Consider email notifications for approaching deadlines
- Add analytics for enrollment trends per period
