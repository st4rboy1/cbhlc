# PR #007: Create Enrollment Period Model and Migration

## Related Ticket

[TICKET-007: Create Enrollment Period Model](./TICKET-007-enrollment-period-model-migration.md)

## Epic

[EPIC-002: Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

## Description

This PR creates the database foundation for enrollment period management by implementing the `enrollment_periods` table migration and EnrollmentPeriod model with validation, scopes, and helper methods.

## Changes Made

### Database Migration

- ✅ Created `create_enrollment_periods_table.php`
- ✅ Added all required fields (school_year, dates, deadlines, status, flags)
- ✅ Added unique constraint on school_year
- ✅ Added indexes for performance
- ✅ Added enum for status (upcoming, active, closed)

### Model

- ✅ Created `app/Models/EnrollmentPeriod.php`
- ✅ Implemented date casts
- ✅ Implemented scopes: `active()`, `upcoming()`, `closed()`
- ✅ Implemented helper methods: `isActive()`, `isOpen()`, `getDaysRemaining()`
- ✅ Added relationship to Enrollment model
- ✅ Implemented boot() with validation logic
- ✅ Enforced "only one active period" rule

### Model Enhancements

- ✅ Date range validation (end > start)
- ✅ Deadline validation (within period dates)
- ✅ Automatic closure of other active periods when activating new one

## Type of Change

- [x] New feature (database migration + model)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Migration Tests

- [ ] Migration runs successfully
- [ ] Migration rollback works
- [ ] Unique constraint on school_year works
- [ ] Indexes created successfully
- [ ] Enum values restricted correctly

### Model Tests

- [ ] Model instantiation works
- [ ] Date casts work correctly
- [ ] `active()` scope returns active periods
- [ ] `upcoming()` scope returns upcoming periods
- [ ] `closed()` scope returns closed periods
- [ ] `isActive()` returns correct boolean
- [ ] `isOpen()` checks both status and dates
- [ ] `getDaysRemaining()` calculates correctly
- [ ] Relationship to enrollments works

### Validation Tests

- [ ] Cannot create period with end_date before start_date
- [ ] Cannot create period with deadline outside date range
- [ ] Only one period can be active at a time
- [ ] Creating new active period closes others

## Verification Steps

```bash
# Run migration
./vendor/bin/sail artisan migrate

# Test model in tinker
./vendor/bin/sail artisan tinker
>>> $period = EnrollmentPeriod::create([
    'school_year' => '2025-2026',
    'start_date' => '2025-06-01',
    'end_date' => '2025-08-31',
    'regular_registration_deadline' => '2025-08-15',
    'status' => 'active',
]);
>>> $period->isActive(); // true
>>> $period->isOpen(); // true (if dates are current)
>>> $period->getDaysRemaining(); // days until deadline

# Test validation
>>> $period2 = EnrollmentPeriod::create([
    'school_year' => '2026-2027',
    'start_date' => '2026-06-01',
    'end_date' => '2026-05-01', // Invalid: end before start
]);
// Should throw exception

# Test only one active
>>> $period3 = EnrollmentPeriod::create([
    'school_year' => '2026-2027',
    'start_date' => '2026-06-01',
    'end_date' => '2026-08-31',
    'regular_registration_deadline' => '2026-08-15',
    'status' => 'active',
]);
>>> EnrollmentPeriod::active()->count(); // Should be 1
>>> $period->fresh()->status; // Should be 'closed'

# Run tests
./vendor/bin/sail pest tests/Unit/Models/EnrollmentPeriodTest.php
./vendor/bin/sail pest tests/Feature/Database/EnrollmentPeriodMigrationTest.php
```

## Model Methods

### Scopes

```php
EnrollmentPeriod::active()->get();    // Get active period(s)
EnrollmentPeriod::upcoming()->get();  // Get upcoming periods
EnrollmentPeriod::closed()->get();    // Get closed periods
```

### Helper Methods

```php
$period->isActive();           // Returns true if status is 'active'
$period->isOpen();             // Returns true if active AND within dates
$period->getDaysRemaining();   // Returns days until deadline
```

### Relationships

```php
$period->enrollments;          // Get all enrollments for this period
```

## Business Logic

### Only One Active Period

When a new period is set to 'active', all other active periods are automatically set to 'closed':

```php
protected static function boot()
{
    parent::boot();

    static::saving(function ($period) {
        if ($period->status === 'active') {
            static::where('status', 'active')
                ->where('id', '!=', $period->id)
                ->update(['status' => 'closed']);
        }
    });
}
```

### Date Validation

```php
if ($period->end_date <= $period->start_date) {
    throw new \InvalidArgumentException('End date must be after start date.');
}

if ($period->regular_registration_deadline < $period->start_date) {
    throw new \InvalidArgumentException('Deadline must be within period dates.');
}
```

## Database Schema

```sql
CREATE TABLE enrollment_periods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(9) UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    early_registration_deadline DATE NULL,
    regular_registration_deadline DATE NOT NULL,
    late_registration_deadline DATE NULL,
    status ENUM('upcoming', 'active', 'closed') DEFAULT 'upcoming',
    description TEXT NULL,
    allow_new_students BOOLEAN DEFAULT TRUE,
    allow_returning_students BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_school_year (school_year)
);
```

## Dependencies

None (First ticket in enrollment period epic)

## Breaking Changes

None

## Deployment Notes

- Run migrations: `php artisan migrate`
- No data seeding required initially
- No downtime expected

## Post-Merge Checklist

- [ ] Migration run successfully on staging
- [ ] Model accessible in codebase
- [ ] Scopes work correctly
- [ ] Helper methods work correctly
- [ ] Business logic enforced (one active period)
- [ ] Tests pass
- [ ] Next ticket (TICKET-008) can begin

## Reviewer Notes

Please verify:

1. Migration follows Laravel conventions
2. School year format validation (YYYY-YYYY)
3. Business logic for "one active period" is sound
4. Date validation logic is correct
5. Scopes are properly implemented
6. Helper methods are accurate
7. Relationships are correctly defined

## Future Enhancements

- Add grace period configuration
- Add notification triggers for period transitions
- Add capacity limits per period
- Add fee configuration per period

---

**Ticket:** #007
**Estimated Effort:** 0.5 day
**Actual Effort:** _[To be filled after completion]_
