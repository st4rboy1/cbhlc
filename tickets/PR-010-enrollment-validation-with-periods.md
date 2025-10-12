# PR #010: Enrollment Validation with Periods

## Related Ticket

[TICKET-010: Enrollment Validation with Periods](./TICKET-010-enrollment-validation-with-periods.md)

## Epic

[EPIC-002: Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

## Description

This PR integrates enrollment period validation into the enrollment submission workflow, ensuring enrollments are only accepted during active periods with appropriate validation for new/returning students and grade level eligibility.

## Changes Made

### Database

- ✅ Migration: Add `enrollment_period_id` to enrollments table
- ✅ Foreign key constraint to enrollment_periods

### Backend

- ✅ Updated `Enrollment` model with period relationship
- ✅ Added `canEnrollForPeriod()` validation method
- ✅ Updated `Guardian/EnrollmentController` with period checks
- ✅ Updated `StoreEnrollmentRequest` with period validation
- ✅ Auto-populate `school_year` from active period

### Frontend

- ✅ Updated enrollment create page with period info
- ✅ Added deadline warning banner
- ✅ Created "No Active Period" page
- ✅ Created "Period Closed" page
- ✅ Added period status to landing page

### API

- ✅ Public API endpoint for period status
- ✅ Used on landing page and enrollment form

## Type of Change

- [x] New feature (enrollment period integration)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Backend Tests

- [ ] Cannot enroll when no active period
- [ ] Cannot enroll when period is closed
- [ ] Can enroll during open period
- [ ] New student validation works
- [ ] Returning student validation works
- [ ] School year auto-populated from period
- [ ] enrollment_period_id set correctly

### Frontend Tests

- [ ] Active period info displays on form
- [ ] Deadline warning shows when < 7 days remaining
- [ ] "No Period" page shows when no active period
- [ ] "Period Closed" page shows when period closed
- [ ] Landing page shows enrollment status
- [ ] Enroll button disabled when period closed

### Integration Tests

- [ ] Full enrollment workflow during open period
- [ ] Blocked enrollment when period closes
- [ ] Period transition handled correctly
- [ ] Error messages are clear and helpful

## Verification Steps

```bash
# Run migration
./vendor/bin/sail artisan migrate

# Run tests
./vendor/bin/sail pest tests/Feature/Enrollment/EnrollmentPeriodValidationTest.php

# Manual testing scenarios:

# Scenario 1: No Active Period
# 1. Ensure no active period exists
# 2. Navigate to enrollment form as guardian
# 3. Should see "No Active Period" page
# 4. Verify enrollment button disabled on landing page

# Scenario 2: Active Period (Enrollment Open)
# 1. Activate an enrollment period
# 2. Navigate to enrollment form
# 3. Should see period info and deadline
# 4. Submit enrollment
# 5. Verify success

# Scenario 3: Period Closed
# 1. Close the active period
# 2. Navigate to enrollment form
# 3. Should see "Period Closed" page
# 4. Verify cannot submit enrollment

# Scenario 4: New Student During Period That Doesn't Allow New Students
# 1. Set active period allow_new_students = false
# 2. Try to enroll new student
# 3. Should see error message

# Test in tinker
./vendor/bin/sail artisan tinker
>>> $period = EnrollmentPeriod::active()->first();
>>> $student = Student::factory()->create();
>>> Enrollment::canEnrollForPeriod($period, $student);
```

## Database Migration

```php
Schema::table('enrollments', function (Blueprint $table) {
    $table->foreignId('enrollment_period_id')
        ->nullable()
        ->after('school_year')
        ->constrained('enrollment_periods')
        ->onDelete('restrict');
});
```

## Model Updates

### Enrollment Model

```php
public function enrollmentPeriod(): BelongsTo
{
    return $this->belongsTo(EnrollmentPeriod::class);
}

public static function canEnrollForPeriod(EnrollmentPeriod $period, Student $student): array
{
    $errors = [];

    if (!$period->isOpen()) {
        $errors[] = 'Enrollment period is not currently open.';
    }

    $isNewStudent = $student->isNewStudent();

    if ($isNewStudent && !$period->allow_new_students) {
        $errors[] = 'This enrollment period does not accept new students.';
    }

    if (!$isNewStudent && !$period->allow_returning_students) {
        $errors[] = 'This enrollment period does not accept returning students.';
    }

    return $errors;
}
```

## Controller Updates

### Guardian/EnrollmentController

```php
public function create()
{
    $activePeriod = EnrollmentPeriod::active()->first();

    if (!$activePeriod) {
        return Inertia::render('Guardian/Enrollments/NoPeriod', [
            'message' => 'Enrollment is currently closed. Please check back later.',
            'nextPeriod' => EnrollmentPeriod::upcoming()->first(),
        ]);
    }

    if (!$activePeriod->isOpen()) {
        return Inertia::render('Guardian/Enrollments/PeriodClosed', [
            'period' => $activePeriod,
            'closedReason' => 'The enrollment deadline has passed.',
        ]);
    }

    return Inertia::render('Guardian/Enrollments/Create', [
        'activePeriod' => $activePeriod,
        'daysRemaining' => $activePeriod->getDaysRemaining(),
        'students' => auth()->user()->students,
        'gradeLevels' => GradeLevel::cases(),
    ]);
}

public function store(StoreEnrollmentRequest $request)
{
    $activePeriod = EnrollmentPeriod::active()->first();

    if (!$activePeriod || !$activePeriod->isOpen()) {
        return back()->withErrors([
            'enrollment' => 'Enrollment period is not currently open.',
        ]);
    }

    $student = Student::findOrFail($request->student_id);
    $errors = Enrollment::canEnrollForPeriod($activePeriod, $student);

    if (!empty($errors)) {
        return back()->withErrors(['enrollment' => $errors]);
    }

    $enrollment = Enrollment::create([
        // ... existing fields
        'enrollment_period_id' => $activePeriod->id,
        'school_year' => $activePeriod->school_year,
    ]);

    return redirect()
        ->route('guardian.enrollments.show', $enrollment)
        ->with('success', 'Enrollment application submitted successfully.');
}
```

## Frontend Updates

### Enrollment Create Page

```tsx
{
    activePeriod && (
        <Alert className="mb-6">
            <InfoIcon className="h-4 w-4" />
            <AlertTitle>Enrollment Open for {activePeriod.school_year}</AlertTitle>
            <AlertDescription>
                <p>Deadline: {formatDate(activePeriod.regular_registration_deadline)}</p>
                {daysRemaining > 0 && daysRemaining <= 7 && (
                    <p className="mt-2 font-semibold text-orange-600">⚠️ Only {daysRemaining} days remaining!</p>
                )}
            </AlertDescription>
        </Alert>
    );
}
```

### No Active Period Page

```tsx
<EmptyState icon={CalendarXIcon} title="Enrollment Currently Closed" description="There is no active enrollment period at this time.">
    {nextPeriod && (
        <p className="mt-4 text-sm text-muted-foreground">
            Next enrollment period: {nextPeriod.school_year}
            <br />
            Opens: {formatDate(nextPeriod.start_date)}
        </p>
    )}
    <Button variant="outline" onClick={() => router.visit('/contact')}>
        Contact School
    </Button>
</EmptyState>
```

### Period Closed Page

```tsx
<Alert variant="warning">
  <AlertCircleIcon className="h-4 w-4" />
  <AlertTitle>Enrollment Period Closed</AlertTitle>
  <AlertDescription>
    The enrollment period for {period.school_year} has ended.
    <br />
    Deadline was: {formatDate(period.regular_registration_deadline)}
  </AlertDescription>
</Alert>

<p className="mt-4">
  For late enrollment inquiries, please contact the registrar's office.
</p>
```

### Landing Page Integration

```tsx
const { periodStatus } = usePage().props;

{
    periodStatus.isOpen ? (
        <Button size="lg" href="/register">
            Enroll Now
        </Button>
    ) : (
        <Button size="lg" disabled>
            Enrollment Closed
        </Button>
    );
}

{
    periodStatus.period && (
        <p className="mt-2 text-sm text-muted-foreground">
            {periodStatus.isOpen
                ? `Deadline: ${formatDate(periodStatus.period.regular_registration_deadline)}`
                : 'Check back later for next enrollment period'}
        </p>
    );
}
```

## Public API

### Period Status Endpoint

```php
Route::get('/api/enrollment-period/status', function () {
    $period = EnrollmentPeriod::active()->first();

    return response()->json([
        'isOpen' => $period && $period->isOpen(),
        'period' => $period,
        'daysRemaining' => $period?->getDaysRemaining() ?? 0,
    ]);
});
```

## Error Messages

- "Enrollment period is not currently open."
- "This enrollment period does not accept new students."
- "This enrollment period does not accept returning students."
- "The enrollment deadline has passed."
- "No active enrollment period at this time."

## Dependencies

- [PR-007](./PR-007-enrollment-period-model-migration.md) - Model must exist
- [PR-008](./PR-008-enrollment-period-crud-backend.md) - CRUD must work
- Existing enrollment system

## Breaking Changes

- Enrollments now require an active period
- Existing enrollments without period_id will need data migration

## Data Migration (if needed)

```php
// Backfill existing enrollments
DB::table('enrollments')
    ->whereNull('enrollment_period_id')
    ->update(['enrollment_period_id' => /* appropriate period ID */]);
```

## Deployment Notes

- Run migration: `php artisan migrate`
- Clear cache: `php artisan cache:clear`
- Build frontend: `npm run build`
- Consider data migration for existing enrollments

## Post-Merge Checklist

- [ ] Migration run successfully
- [ ] Cannot enroll without active period
- [ ] Cannot enroll when period closed
- [ ] Can enroll during open period
- [ ] Error messages are clear
- [ ] Frontend pages display correctly
- [ ] Landing page shows correct status
- [ ] No console errors
- [ ] Next ticket (TICKET-011) can begin

## Reviewer Notes

Please verify:

1. Period validation is comprehensive
2. Error messages are user-friendly
3. Frontend gracefully handles all states
4. No enrollment bypasses period check
5. Auto-population of school_year works
6. Foreign key constraint is appropriate
7. Migration is safe for production
8. Tests cover all scenarios

---

**Ticket:** #010
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_
