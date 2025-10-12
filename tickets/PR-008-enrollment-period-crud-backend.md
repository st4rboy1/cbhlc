# PR #008: Enrollment Period CRUD Backend

## Related Ticket

[TICKET-008: Enrollment Period CRUD Backend](./TICKET-008-enrollment-period-crud-backend.md)

## Epic

[EPIC-002: Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

## Description

This PR implements full CRUD operations for enrollment periods with custom actions (activate, close), comprehensive validation, activity logging, and Super Admin-only access control.

## Changes Made

### Controller

- ✅ Created `SuperAdmin/EnrollmentPeriodController.php`
- ✅ Implemented full resource methods (index, create, store, show, edit, update, destroy)
- ✅ Implemented custom `activate()` method
- ✅ Implemented custom `close()` method
- ✅ Added enrollment count to list view

### Validation

- ✅ Created `StoreEnrollmentPeriodRequest`
- ✅ Created `UpdateEnrollmentPeriodRequest`
- ✅ School year format validation (YYYY-YYYY)
- ✅ Date range validation
- ✅ Deadline validation
- ✅ Unique school year constraint

### Routes

- ✅ Added resource routes for enrollment periods
- ✅ Added custom routes for activate/close actions
- ✅ Applied `role:super_admin` middleware

### Activity Logging

- ✅ Log period creation
- ✅ Log period updates (with old/new values)
- ✅ Log period activation
- ✅ Log period closure
- ✅ Log period deletion

### Business Logic

- ✅ Prevent deletion of active periods
- ✅ Prevent deletion of periods with enrollments
- ✅ Auto-close other periods when activating new one

## Type of Change

- [x] New feature (backend CRUD)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Feature Tests

- [ ] Super Admin can list all periods
- [ ] Super Admin can create new period
- [ ] Super Admin can view period details
- [ ] Super Admin can update period
- [ ] Super Admin can delete period (without enrollments)
- [ ] Super Admin can activate period
- [ ] Super Admin can close period
- [ ] Cannot delete active period
- [ ] Cannot delete period with enrollments
- [ ] Activating period closes others
- [ ] Activity logged for all actions

### Validation Tests

- [ ] School year format validated (YYYY-YYYY)
- [ ] Start date must be before end date
- [ ] Regular deadline must be within period dates
- [ ] Early deadline must be before regular deadline
- [ ] Late deadline must be after regular deadline
- [ ] School year must be unique
- [ ] All required fields validated

### Authorization Tests

- [ ] Only Super Admin can access routes
- [ ] Administrator cannot access
- [ ] Registrar cannot access
- [ ] Guardian cannot access
- [ ] Student cannot access

## Verification Steps

```bash
# Run feature tests
./vendor/bin/sail pest tests/Feature/SuperAdmin/EnrollmentPeriodTest.php

# Manual API testing with Postman/curl

# List periods
curl -X GET http://localhost/super-admin/enrollment-periods \
  -H "Authorization: Bearer {super_admin_token}"

# Create period
curl -X POST http://localhost/super-admin/enrollment-periods \
  -H "Authorization: Bearer {super_admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "school_year": "2025-2026",
    "start_date": "2025-06-01",
    "end_date": "2025-08-31",
    "regular_registration_deadline": "2025-08-15",
    "allow_new_students": true,
    "allow_returning_students": true
  }'

# Activate period
curl -X POST http://localhost/super-admin/enrollment-periods/1/activate \
  -H "Authorization: Bearer {super_admin_token}"

# Verify activity log
./vendor/bin/sail artisan tinker
>>> Activity::where('subject_type', 'App\\Models\\EnrollmentPeriod')->latest()->get();
```

## API Endpoints

### List Enrollment Periods

```
GET /super-admin/enrollment-periods
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "school_year": "2025-2026",
      "start_date": "2025-06-01",
      "end_date": "2025-08-31",
      "regular_registration_deadline": "2025-08-15",
      "status": "active",
      "enrollments_count": 45,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Create Enrollment Period

```
POST /super-admin/enrollment-periods
```

**Request:**

```json
{
    "school_year": "2026-2027",
    "start_date": "2026-06-01",
    "end_date": "2026-08-31",
    "early_registration_deadline": "2026-07-15",
    "regular_registration_deadline": "2026-08-15",
    "late_registration_deadline": "2026-08-25",
    "description": "School year 2026-2027 enrollment",
    "allow_new_students": true,
    "allow_returning_students": true
}
```

### Update Enrollment Period

```
PUT /super-admin/enrollment-periods/{id}
```

### Activate Period

```
POST /super-admin/enrollment-periods/{id}/activate
```

**Response:**

```json
{
    "message": "Enrollment period activated successfully"
}
```

### Close Period

```
POST /super-admin/enrollment-periods/{id}/close
```

### Delete Period

```
DELETE /super-admin/enrollment-periods/{id}
```

## Validation Rules

### StoreEnrollmentPeriodRequest

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

## Activity Logging Examples

### Period Created

```php
activity()
    ->performedOn($period)
    ->withProperties($period->toArray())
    ->log('Enrollment period created');
```

### Period Activated

```php
activity()
    ->performedOn($period)
    ->log('Enrollment period activated');
```

### Period Updated

```php
activity()
    ->performedOn($period)
    ->withProperties([
        'old' => $old,
        'new' => $period->toArray(),
    ])
    ->log('Enrollment period updated');
```

## Business Logic

### Prevent Deletion of Active Period

```php
if ($period->isActive()) {
    return back()->withErrors([
        'period' => 'Cannot delete an active enrollment period.'
    ]);
}
```

### Prevent Deletion with Enrollments

```php
if ($period->enrollments()->exists()) {
    return back()->withErrors([
        'period' => 'Cannot delete period with existing enrollments.'
    ]);
}
```

## Dependencies

- [PR-007](./PR-007-enrollment-period-model-migration.md) - Model must exist

## Breaking Changes

None

## Deployment Notes

- Run migrations (none needed, uses existing table)
- Clear route cache: `php artisan route:clear`
- Ensure Super Admin role exists

## Post-Merge Checklist

- [ ] Routes accessible to Super Admin
- [ ] CRUD operations work correctly
- [ ] Activate/close actions work
- [ ] Validation prevents invalid data
- [ ] Activity logged for all actions
- [ ] Authorization enforced correctly
- [ ] Next ticket (TICKET-009) can begin

## Reviewer Notes

Please verify:

1. All CRUD operations are implemented correctly
2. Validation is comprehensive and secure
3. Authorization is properly enforced (Super Admin only)
4. Activity logging captures all necessary information
5. Business logic prevents data inconsistencies
6. Error messages are clear and helpful
7. Code follows Laravel conventions
8. Tests cover all scenarios

## Error Responses

### Validation Error (422)

```json
{
    "message": "The school year must match the format YYYY-YYYY.",
    "errors": {
        "school_year": ["The school year must match the format YYYY-YYYY."]
    }
}
```

### Authorization Error (403)

```json
{
    "message": "This action is unauthorized."
}
```

### Business Logic Error (400)

```json
{
    "message": "Cannot delete period with existing enrollments.",
    "errors": {
        "period": ["Cannot delete period with existing enrollments."]
    }
}
```

---

**Ticket:** #008
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_
