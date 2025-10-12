# Ticket #007: Create Enrollment Period Model and Migration

**Epic:** [EPIC-002 Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 0.5 day
**Assignee:** TBD

## Description

Create database migration and Eloquent model for EnrollmentPeriod to manage school year enrollment windows with deadlines and status tracking.

## Acceptance Criteria

- [ ] Migration created: `create_enrollment_periods_table.php`
- [ ] EnrollmentPeriod model created
- [ ] Model includes validation for date ranges
- [ ] Enum for status: upcoming, active, closed
- [ ] Scopes: `active()`, `upcoming()`, `closed()`
- [ ] Helper methods: `isActive()`, `isOpen()`, `getDaysRemaining()`
- [ ] Migration can be run and rolled back successfully

## Implementation Details

### Migration Schema

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

    // Indexes
    $table->index('status');
    $table->index('school_year');
});
```

### Model Methods

```php
class EnrollmentPeriod extends Model
{
    protected $fillable = [
        'school_year',
        'start_date',
        'end_date',
        'early_registration_deadline',
        'regular_registration_deadline',
        'late_registration_deadline',
        'status',
        'description',
        'allow_new_students',
        'allow_returning_students',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'early_registration_deadline' => 'date',
        'regular_registration_deadline' => 'date',
        'late_registration_deadline' => 'date',
        'allow_new_students' => 'boolean',
        'allow_returning_students' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOpen(): bool
    {
        return $this->isActive() && now()->between($this->start_date, $this->end_date);
    }

    public function getDaysRemaining(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->regular_registration_deadline, false));
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'school_year', 'school_year');
    }
}
```

### Validation Rules

```php
protected static function boot()
{
    parent::boot();

    static::saving(function ($period) {
        // Validate date ranges
        if ($period->end_date <= $period->start_date) {
            throw new \InvalidArgumentException('End date must be after start date.');
        }

        if ($period->regular_registration_deadline < $period->start_date) {
            throw new \InvalidArgumentException('Registration deadline must be within period dates.');
        }

        // Only one active period at a time
        if ($period->status === 'active') {
            static::where('status', 'active')
                ->where('id', '!=', $period->id)
                ->update(['status' => 'closed']);
        }
    });
}
```

## Testing Requirements

- [ ] Migration runs successfully
- [ ] Migration rollback works
- [ ] Model can be instantiated
- [ ] Scopes return correct results
- [ ] isActive() method works
- [ ] isOpen() method works
- [ ] getDaysRemaining() calculates correctly
- [ ] Only one active period validation works
- [ ] Date range validation works

## Dependencies

None

## Notes

- School year format: "YYYY-YYYY" (e.g., "2025-2026")
- Ensure only one active period globally
- Add indexes for performance on status and school_year
