# PR #015: Verify and Enhance Audit Logging Coverage

## Related Ticket

[TICKET-015: Verify Audit Logging Coverage](./TICKET-015-verify-audit-logging-coverage.md)

## Epic

[EPIC-007: Audit System Verification](./EPIC-007-audit-system-verification.md)

## Description

This PR ensures comprehensive audit logging across all critical models and actions by verifying Spatie Activity Log configuration, adding LogsActivity traits where missing, and implementing custom logging for important workflows like enrollment approval/rejection and authentication events.

## Changes Made

### Model Enhancements

- ✅ Added LogsActivity trait to all critical models
- ✅ Configured LogOptions for each model
- ✅ Implemented custom logging methods for key actions
- ✅ Models updated: Enrollment, Student, Guardian, User, GradeLevelFee, Invoice, Payment

### Authentication Logging

- ✅ Created `LogAuthenticationActivity` listener
- ✅ Log login attempts (success and failure)
- ✅ Log logout events
- ✅ Track IP addresses and user agents
- ✅ Registered listeners in EventServiceProvider

### Custom Action Logging

- ✅ Enrollment approval/rejection with context
- ✅ Document verification/rejection with reasons
- ✅ Permission/role changes
- ✅ Payment processing
- ✅ Invoice creation

### Configuration Verification

- ✅ Verified `config/activitylog.php` settings
- ✅ Confirmed retention policy (365 days)
- ✅ Verified model configuration

## Type of Change

- [x] Enhancement (audit logging)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Model Tests

- [ ] All critical models have LogsActivity trait
- [ ] LogOptions configured for each model
- [ ] Model CRUD operations logged
- [ ] Only dirty attributes logged
- [ ] Empty logs not submitted

### Authentication Tests

- [ ] Successful login logged
- [ ] Failed login logged
- [ ] Logout logged
- [ ] IP address captured
- [ ] User agent captured

### Custom Action Tests

- [ ] Enrollment approval logged with details
- [ ] Enrollment rejection logged with reason
- [ ] Document verification logged
- [ ] Role assignment logged
- [ ] Permission changes logged

### Integration Tests

- [ ] Activity log accessible via model
- [ ] Causer tracked correctly
- [ ] Properties stored correctly
- [ ] Timestamps accurate

## Verification Steps

```bash
# Run tests
./vendor/bin/sail pest tests/Feature/ActivityLog/AuditLoggingTest.php

# Test in tinker
./vendor/bin/sail artisan tinker

# Test enrollment approval logging
>>> $enrollment = Enrollment::first();
>>> $admin = User::role('registrar')->first();
>>> auth()->login($admin);
>>> $enrollment->approve($admin);
>>> Activity::where('subject_id', $enrollment->id)->latest()->first();
// Should show approval activity with properties

# Test login logging
>>> auth()->logout();
>>> auth()->attempt(['email' => 'test@example.com', 'password' => 'wrong']);
>>> Activity::where('description', 'Failed login attempt')->latest()->first();
// Should show failed attempt with IP and email

# Test model changes
>>> $student = Student::first();
>>> $student->update(['first_name' => 'NewName']);
>>> $student->activities()->latest()->first();
// Should show only changed attributes

# View all activities for a model
>>> $enrollment->activities;

# View all activities by a user
>>> Activity::causedBy($admin)->get();
```

## Model Implementations

### Enrollment Model

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Enrollment extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Enrollment application created',
                'updated' => 'Enrollment application updated',
                'deleted' => 'Enrollment application deleted',
                default => "Enrollment {$eventName}",
            });
    }

    public function approve(User $approver)
    {
        activity()
            ->performedOn($this)
            ->causedBy($approver)
            ->withProperties([
                'enrollment_id' => $this->enrollment_id,
                'student_name' => $this->student->full_name,
                'grade_level' => $this->grade_level->value,
                'previous_status' => $this->status->value,
                'new_status' => 'approved',
            ])
            ->log('Enrollment approved');

        $this->update([
            'status' => EnrollmentStatus::APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $approver, string $reason)
    {
        activity()
            ->performedOn($this)
            ->causedBy($approver)
            ->withProperties([
                'enrollment_id' => $this->enrollment_id,
                'student_name' => $this->student->full_name,
                'rejection_reason' => $reason,
                'previous_status' => $this->status->value,
                'new_status' => 'rejected',
            ])
            ->log('Enrollment rejected');

        $this->update([
            'status' => EnrollmentStatus::REJECTED,
            'remarks' => $reason,
            'rejected_at' => now(),
        ]);
    }
}
```

### User Model

```php
class User extends Authenticatable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Override role assignment to log
    public function assignRole($roles, $guard = null)
    {
        $result = parent::assignRole($roles, $guard);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties([
                'roles' => is_array($roles) ? $roles : [$roles],
            ])
            ->log('User role assigned');

        return $result;
    }
}
```

### Authentication Listener

```php
class LogAuthenticationActivity
{
    public function handleLogin(Login $event)
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'guard' => $event->guard,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('User logged in');
    }

    public function handleLogout(Logout $event)
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'guard' => $event->guard,
                'ip_address' => request()->ip(),
            ])
            ->log('User logged out');
    }

    public function handleFailed(Failed $event)
    {
        activity()
            ->withProperties([
                'email' => $event->credentials['email'] ?? 'unknown',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard,
            ])
            ->log('Failed login attempt');
    }
}
```

### Event Service Provider

```php
protected $listen = [
    Login::class => [LogAuthenticationActivity::class . '@handleLogin'],
    Logout::class => [LogAuthenticationActivity::class . '@handleLogout'],
    Failed::class => [LogAuthenticationActivity::class . '@handleFailed'],
];
```

## Models with LogsActivity

### Confirmed Implementation

- [x] Enrollment - All fields, custom approval/rejection logging
- [x] Student - Personal info only (name, email, address)
- [x] Guardian - Contact info only
- [x] User - Name and email only
- [x] GradeLevelFee - All fee changes
- [x] Invoice - Creation and status changes
- [x] Payment - All payment activities
- [ ] Document - Add when implemented (TICKET-001)

## Configuration Verification

### config/activitylog.php

```php
return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 365,
    'default_log_name' => 'default',
    'default_auth_driver' => null,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
];
```

## Activity Log Examples

### Enrollment Approved

```json
{
    "description": "Enrollment approved",
    "subject_type": "App\\Models\\Enrollment",
    "subject_id": 1,
    "causer_type": "App\\Models\\User",
    "causer_id": 2,
    "properties": {
        "enrollment_id": "2025-0001",
        "student_name": "John Doe",
        "grade_level": "grade_1",
        "previous_status": "pending",
        "new_status": "approved"
    }
}
```

### Failed Login

```json
{
    "description": "Failed login attempt",
    "properties": {
        "email": "test@example.com",
        "ip_address": "192.168.1.1",
        "user_agent": "Mozilla/5.0...",
        "guard": "web"
    }
}
```

### Student Updated

```json
{
    "description": "Student updated",
    "subject_type": "App\\Models\\Student",
    "subject_id": 1,
    "causer_id": 2,
    "properties": {
        "attributes": {
            "first_name": "Jane",
            "email": "jane@example.com"
        },
        "old": {
            "first_name": "John",
            "email": "john@example.com"
        }
    }
}
```

## Dependencies

- Spatie Activity Log package (should be installed)
- Activity log migrations run

## Breaking Changes

None

## Deployment Notes

- Verify Spatie Activity Log is installed: `composer show spatie/laravel-activitylog`
- Run any pending migrations
- Clear cache: `php artisan cache:clear`
- Monitor storage usage for activity_log table

## Post-Merge Checklist

- [ ] All critical models have LogsActivity
- [ ] Authentication events logged
- [ ] Custom actions logged with context
- [ ] Activity log accessible via relationships
- [ ] No sensitive data logged (passwords, tokens)
- [ ] Performance acceptable
- [ ] Storage usage monitored
- [ ] Next ticket (TICKET-016) can begin

## Reviewer Notes

Please verify:

1. All critical models identified and updated
2. LogOptions configured appropriately (not logging too much)
3. Custom logging captures sufficient context
4. Authentication logging doesn't expose sensitive data
5. Performance impact is acceptable
6. Storage retention policy is reasonable
7. No N+1 query issues introduced
8. Tests cover all logging scenarios

## Security Considerations

- Don't log passwords or tokens
- Don't log full request bodies
- Be careful with PII in logs
- Implement log retention policy
- Restrict access to activity logs (admin only)
- Consider encrypting sensitive properties

## Performance Considerations

- LogOnlyDirty prevents excessive logging
- dontSubmitEmptyLogs reduces noise
- Activity log table will grow over time
- Consider partitioning or archiving old logs
- Index on created_at for cleanup queries
- Monitor query performance on activity_log

---

**Ticket:** #015
**Estimated Effort:** 1 day
**Actual Effort:** _[To be filled after completion]_
