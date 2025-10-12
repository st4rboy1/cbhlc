# Ticket #010: Enrollment Validation with Periods

**Epic:** [EPIC-002 Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Assignee:** TBD

## Description

Integrate enrollment period validation into the enrollment submission workflow to ensure enrollments are only accepted during active periods with appropriate validation rules.

## Acceptance Criteria

- [ ] Enrollment submission validates against active period
- [ ] Clear error messages when period is closed
- [ ] Warning messages when approaching deadline
- [ ] Display active period info on enrollment form
- [ ] Prevent enrollment if no active period
- [ ] Respect new/returning student flags
- [ ] Update Enrollment model to reference period
- [ ] Migration to add period relationship

## Implementation Details

### Database Update

Add foreign key to enrollments table:

```php
// Migration: add_enrollment_period_id_to_enrollments_table.php
Schema::table('enrollments', function (Blueprint $table) {
    $table->foreignId('enrollment_period_id')
        ->nullable()
        ->after('school_year')
        ->constrained('enrollment_periods')
        ->onDelete('restrict');
});
```

### Update Enrollment Model

```php
class Enrollment extends Model
{
    protected $fillable = [
        // ... existing fields
        'enrollment_period_id',
    ];

    public function enrollmentPeriod(): BelongsTo
    {
        return $this->belongsTo(EnrollmentPeriod::class);
    }

    // Validation method
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
}
```

### Update Enrollment Controller

**Guardian/EnrollmentController**

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

    // Validate enrollment eligibility
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

### Validation Request Update

**StoreEnrollmentRequest**

```php
public function rules()
{
    return [
        'student_id' => [
            'required',
            'exists:students,id',
            function ($attribute, $value, $fail) {
                $activePeriod = EnrollmentPeriod::active()->first();

                if (!$activePeriod) {
                    $fail('Enrollment period is not currently open.');
                    return;
                }

                $student = Student::find($value);
                $errors = Enrollment::canEnrollForPeriod($activePeriod, $student);

                if (!empty($errors)) {
                    $fail(implode(' ', $errors));
                }
            },
        ],
        // ... other fields
    ];
}
```

### Frontend Updates

**Enrollment Create Page**

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

**No Active Period Page**
`resources/js/pages/guardian/enrollments/no-period.tsx`

Display:

- Message that enrollment is closed
- Next upcoming period (if available)
- Contact information for inquiries

**Period Closed Page**
`resources/js/pages/guardian/enrollments/period-closed.tsx`

Display:

- Period information
- Reason for closure (deadline passed)
- Instructions for late enrollment (if available)

### Public API for Period Status

```php
// Route
Route::get('/api/enrollment-period/status', function () {
    $period = EnrollmentPeriod::active()->first();

    return response()->json([
        'isOpen' => $period && $period->isOpen(),
        'period' => $period,
        'daysRemaining' => $period?->getDaysRemaining() ?? 0,
    ]);
});
```

### Landing Page Integration

Update landing page to show enrollment status:

```tsx
{
    periodStatus.isOpen ? <Button href="/register">Enroll Now</Button> : <Button disabled>Enrollment Closed</Button>;
}
```

## Testing Requirements

- [ ] Feature test: cannot enroll when no active period
- [ ] Feature test: cannot enroll when period is closed
- [ ] Feature test: new student validation
- [ ] Feature test: returning student validation
- [ ] Feature test: successful enrollment during open period
- [ ] Feature test: school year auto-populated from period
- [ ] UI test: deadline warning shows
- [ ] UI test: no period page displays
- [ ] UI test: period closed page displays
- [ ] Integration test: full enrollment workflow with period

## Dependencies

- [TICKET-007](./TICKET-007-enrollment-period-model-migration.md) - EnrollmentPeriod model
- [TICKET-008](./TICKET-008-enrollment-period-crud-backend.md) - Backend CRUD
- Existing enrollment system

## Notes

- Consider grace period for late enrollments
- Add email notification when period opens
- Log enrollment attempts during closed periods for analytics
- Consider waitlist feature for closed periods
