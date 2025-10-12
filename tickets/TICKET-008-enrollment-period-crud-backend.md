# Ticket #008: Enrollment Period CRUD Backend

**Epic:** [EPIC-002 Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Assignee:** TBD

## Description

Implement backend CRUD operations for enrollment periods allowing Super Admin to create, view, update, and manage enrollment periods.

## Acceptance Criteria

- [ ] `SuperAdmin/EnrollmentPeriodController` created
- [ ] Full CRUD routes implemented
- [ ] Custom actions: activate, close
- [ ] Validation rules for all inputs
- [ ] Only Super Admin can manage periods
- [ ] Activity logging for all actions
- [ ] Prevent deletion of active periods

## Implementation Details

### Controller

`app/Http/Controllers/SuperAdmin/EnrollmentPeriodController.php`

### Routes

```php
Route::prefix('super-admin')->name('super-admin.')->middleware('role:super_admin')->group(function () {
    Route::resource('enrollment-periods', EnrollmentPeriodController::class);
    Route::post('/enrollment-periods/{period}/activate', [EnrollmentPeriodController::class, 'activate'])
        ->name('enrollment-periods.activate');
    Route::post('/enrollment-periods/{period}/close', [EnrollmentPeriodController::class, 'close'])
        ->name('enrollment-periods.close');
});
```

### Key Controller Methods

**Index**

```php
public function index()
{
    $periods = EnrollmentPeriod::latest('school_year')
        ->withCount('enrollments')
        ->paginate(10);

    $activePeriod = EnrollmentPeriod::active()->first();

    return Inertia::render('SuperAdmin/EnrollmentPeriods/Index', [
        'periods' => $periods,
        'activePeriod' => $activePeriod,
    ]);
}
```

**Store**

```php
public function store(StoreEnrollmentPeriodRequest $request)
{
    $period = EnrollmentPeriod::create($request->validated());

    activity()
        ->performedOn($period)
        ->withProperties($period->toArray())
        ->log('Enrollment period created');

    return redirect()
        ->route('super-admin.enrollment-periods.index')
        ->with('success', 'Enrollment period created successfully.');
}
```

**Update**

```php
public function update(UpdateEnrollmentPeriodRequest $request, EnrollmentPeriod $period)
{
    $old = $period->toArray();

    $period->update($request->validated());

    activity()
        ->performedOn($period)
        ->withProperties([
            'old' => $old,
            'new' => $period->toArray(),
        ])
        ->log('Enrollment period updated');

    return redirect()
        ->route('super-admin.enrollment-periods.show', $period)
        ->with('success', 'Enrollment period updated successfully.');
}
```

**Activate**

```php
public function activate(EnrollmentPeriod $period)
{
    // Close other active periods
    EnrollmentPeriod::where('status', 'active')->update(['status' => 'closed']);

    $period->update(['status' => 'active']);

    activity()
        ->performedOn($period)
        ->log('Enrollment period activated');

    return back()->with('success', 'Enrollment period activated successfully.');
}
```

**Close**

```php
public function close(EnrollmentPeriod $period)
{
    if (!$period->isActive()) {
        return back()->withErrors(['period' => 'Only active periods can be closed.']);
    }

    $period->update(['status' => 'closed']);

    activity()
        ->performedOn($period)
        ->log('Enrollment period closed');

    return back()->with('success', 'Enrollment period closed successfully.');
}
```

**Destroy**

```php
public function destroy(EnrollmentPeriod $period)
{
    // Prevent deletion of active period
    if ($period->isActive()) {
        return back()->withErrors([
            'period' => 'Cannot delete an active enrollment period.'
        ]);
    }

    // Prevent deletion if enrollments exist
    if ($period->enrollments()->exists()) {
        return back()->withErrors([
            'period' => 'Cannot delete period with existing enrollments.'
        ]);
    }

    activity()
        ->performedOn($period)
        ->withProperties($period->toArray())
        ->log('Enrollment period deleted');

    $period->delete();

    return redirect()
        ->route('super-admin.enrollment-periods.index')
        ->with('success', 'Enrollment period deleted successfully.');
}
```

### Validation Requests

**StoreEnrollmentPeriodRequest**

```php
public function rules()
{
    return [
        'school_year' => 'required|string|regex:/^\d{4}-\d{4}$/|unique:enrollment_periods,school_year',
        'start_date' => 'required|date|after:yesterday',
        'end_date' => 'required|date|after:start_date',
        'early_registration_deadline' => 'nullable|date|after_or_equal:start_date|before:end_date',
        'regular_registration_deadline' => 'required|date|after_or_equal:start_date|before_or_equal:end_date',
        'late_registration_deadline' => 'nullable|date|after:regular_registration_deadline|before_or_equal:end_date',
        'description' => 'nullable|string|max:1000',
        'allow_new_students' => 'boolean',
        'allow_returning_students' => 'boolean',
    ];
}
```

**UpdateEnrollmentPeriodRequest**

```php
public function rules()
{
    return [
        'school_year' => [
            'required',
            'string',
            'regex:/^\d{4}-\d{4}$/',
            Rule::unique('enrollment_periods')->ignore($this->enrollment_period),
        ],
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'early_registration_deadline' => 'nullable|date|after_or_equal:start_date|before:end_date',
        'regular_registration_deadline' => 'required|date|after_or_equal:start_date|before_or_equal:end_date',
        'late_registration_deadline' => 'nullable|date|after:regular_registration_deadline|before_or_equal:end_date',
        'description' => 'nullable|string|max:1000',
        'allow_new_students' => 'boolean',
        'allow_returning_students' => 'boolean',
    ];
}
```

## Testing Requirements

- [ ] Feature test: create enrollment period
- [ ] Feature test: update enrollment period
- [ ] Feature test: delete enrollment period
- [ ] Feature test: activate period (closes others)
- [ ] Feature test: close period
- [ ] Feature test: cannot delete active period
- [ ] Feature test: cannot delete period with enrollments
- [ ] Validation test: date range validation
- [ ] Validation test: school year format
- [ ] Authorization test: only super_admin access

## Dependencies

- [TICKET-007](./TICKET-007-enrollment-period-model-migration.md) - Model must exist
- Activity log package
- Super Admin role

## Notes

- Log all period changes for audit trail
- Consider adding email notifications when periods change status
- Add confirmation dialogs for critical actions (activate, close, delete)
