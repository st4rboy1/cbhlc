# Ticket #015: Verify and Enhance Audit Logging Coverage

**Epic:** [EPIC-007 Audit System Verification](./EPIC-007-audit-system-verification.md)

**Type:** Story
**Priority:** High
**Estimated Effort:** 1 day
**Assignee:** TBD

## Description

Verify that Spatie Activity Log is properly configured and all critical models have comprehensive audit logging. Add missing LogsActivity traits and custom logging for important actions.

## Acceptance Criteria

- [ ] All critical models have LogsActivity trait
- [ ] LogOptions configured for each model
- [ ] Custom action logging added (approve, reject, etc.)
- [ ] Login/logout attempts logged
- [ ] Permission/role changes logged
- [ ] Activity log configuration verified
- [ ] Test coverage for logging functionality

## Implementation Details

### Models to Verify/Update

**Critical Models (Must Have Logging):**

1. User
2. Student
3. Guardian
4. Enrollment
5. GradeLevelFee
6. Invoice
7. Payment
8. Document (when implemented)

### Add LogsActivity Trait

**Example: Enrollment Model**

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

    // Custom logging methods
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

**User Model**

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

    // Log role assignment
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

### Authentication Event Logging

Create listener for authentication events:

`app/Listeners/LogAuthenticationActivity.php`

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Attempting;

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

Register in `EventServiceProvider`:

```php
protected $listen = [
    Login::class => [LogAuthenticationActivity::class . '@handleLogin'],
    Logout::class => [LogAuthenticationActivity::class . '@handleLogout'],
    Failed::class => [LogAuthenticationActivity::class . '@handleFailed'],
];
```

### Verify Configuration

Check `config/activitylog.php`:

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

### Middleware for Request Logging (Optional)

```php
class LogHttpRequests
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Log important requests
        if ($this->shouldLog($request)) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'status_code' => $response->status(),
                ])
                ->log('HTTP request');
        }

        return $response;
    }

    protected function shouldLog($request): bool
    {
        // Only log POST, PUT, PATCH, DELETE
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }
}
```

## Testing Requirements

- [ ] Unit test: LogsActivity trait on all models
- [ ] Feature test: enrollment approve logs activity
- [ ] Feature test: enrollment reject logs activity
- [ ] Feature test: login logs activity
- [ ] Feature test: logout logs activity
- [ ] Feature test: failed login logs activity
- [ ] Feature test: role assignment logs activity
- [ ] Verify logs contain expected properties

## Dependencies

- Spatie Activity Log package (verify installed)
- Activity log migrations run

## Notes

- Don't log sensitive data (passwords, tokens)
- Consider performance impact of logging all changes
- Implement log rotation/cleanup for old entries
- Add log retention policy
