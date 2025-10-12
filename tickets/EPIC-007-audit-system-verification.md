# Ticket #007: Audit System Verification and Enhancement

## Priority: High (Should Have)

## Related SRS Requirements

- **Section 8.2.2:** AUDIT_LOG entity (Supporting Entities in ERD)
- **FR-4.4:** System shall maintain audit trail of all application actions
- **NFR-2.4:** Comprehensive audit logging using Laravel's event system and model observers
- **Section 6.2:** Security Requirements

## Current Status

⚠️ **PARTIALLY IMPLEMENTED**

Current implementation:

- `activity_log` tables exist (Spatie Activity Log package)
- Some model observers may log changes
- Missing comprehensive audit log viewer UI
- Need verification of audit coverage

## Required Verification

### 1. Verify Existing Implementation

**Check if Spatie Activity Log is installed:**

```bash
composer show spatie/laravel-activitylog
```

**Verify migrations exist:**

- `2025_09_24_215203_create_activity_log_table.php`
- `2025_09_24_215204_add_event_column_to_activity_log_table.php`
- `2025_09_24_215205_add_batch_uuid_column_to_activity_log_table.php`

**Check models for LogsActivity trait:**

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ModelName extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['field1', 'field2'])
            ->logOnlyDirty();
    }
}
```

### 2. Required Enhancements

#### a) Ensure All Critical Models Log Activities

**Models that MUST have audit logging:**

- User (account changes, login attempts)
- Student (all changes)
- Guardian (all changes)
- Enrollment (status changes, approvals, rejections)
- GradeLevelFee (fee changes)
- Invoice (creation, status changes)
- Payment (all payment activities)
- Document (uploads, verifications) - if implemented

**Example Implementation:**

```php
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
                'created' => 'Enrollment application submitted',
                'updated' => 'Enrollment application updated',
                'deleted' => 'Enrollment application deleted',
            });
    }

    // Custom logging for specific actions
    public function approve(User $approver)
    {
        activity()
            ->performedOn($this)
            ->causedBy($approver)
            ->withProperties([
                'old_status' => $this->status,
                'new_status' => 'approved',
                'enrollment_id' => $this->enrollment_id,
                'student_name' => $this->student->full_name,
            ])
            ->log('Enrollment approved');

        $this->update([
            'status' => EnrollmentStatus::APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }
}
```

#### b) Login Attempt Logging

**Create Listener for Login Events:**

```php
// app/Listeners/LogAuthenticationAttempt.php
class LogAuthenticationAttempt
{
    public function handle(Login|Failed $event)
    {
        activity()
            ->causedBy($event->user ?? null)
            ->withProperties([
                'email' => $event->credentials['email'] ?? $event->user?->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'success' => $event instanceof Login,
            ])
            ->log($event instanceof Login ? 'User logged in' : 'Login failed');
    }
}

// Register in EventServiceProvider
protected $listen = [
    Login::class => [LogAuthenticationAttempt::class],
    Failed::class => [LogAuthenticationAttempt::class],
    Logout::class => [LogAuthenticationAttempt::class],
];
```

#### c) Permission and Role Changes Logging

```php
// Log when user permissions/roles change
User::find($userId)->assignRole('registrar');

activity()
    ->performedOn($user)
    ->causedBy(auth()->user())
    ->withProperties([
        'role_assigned' => 'registrar',
    ])
    ->log('User role assigned');
```

### 3. Backend Layer

**Controllers:**

- `SuperAdmin/AuditLogController.php` - View all audit logs
- `Admin/AuditLogController.php` - View relevant audit logs
- `Registrar/AuditLogController.php` - View enrollment-related logs

**Routes:**

```php
// Super Admin routes
Route::prefix('super-admin/audit-logs')->name('super-admin.audit-logs.')->group(function () {
    Route::get('/', [SuperAdminAuditLogController::class, 'index'])->name('index');
    Route::get('/{activity}', 'show')->name('show');
    Route::get('/model/{type}/{id}', 'forModel')->name('for-model');
    Route::get('/user/{user}', 'forUser')->name('for-user');
    Route::post('/export', 'export')->name('export');
});

// Admin routes
Route::prefix('admin/audit-logs')->name('admin.audit-logs.')->group(function () {
    Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
});
```

**Query Methods:**

```php
// Get all activities for a model
Activity::forSubject($enrollment)->get();

// Get all activities by a user
Activity::causedBy($user)->get();

// Get activities with filters
Activity::where('description', 'like', '%approved%')
    ->where('created_at', '>=', now()->subDays(7))
    ->get();
```

### 4. Frontend Layer

**Pages:**

- `/resources/js/pages/super-admin/audit-logs/index.tsx` - All audit logs
- `/resources/js/pages/admin/audit-logs/index.tsx` - Filtered audit logs
- `/resources/js/pages/super-admin/audit-logs/show.tsx` - Detail view

**Components:**

- `AuditLogTable` - Sortable, filterable log table
- `AuditLogCard` - Individual log entry display
- `AuditLogTimeline` - Timeline view of related activities
- `AuditLogFilters` - Advanced filtering component
- `AuditLogDiff` - Visual diff of changes

**Features:**

- Paginated log display
- Advanced filtering:
    - By user (causer)
    - By model type and ID
    - By action type (created, updated, deleted)
    - By date range
    - By description/keywords
- Sort by date, user, action
- Visual diff of before/after values
- Export logs to CSV/Excel
- Link to related records
- User avatar and name display
- IP address and user agent display

### 5. Audit Log Viewer UI

**Table Columns:**

- Timestamp
- User (causer) with avatar
- Action/Event
- Model Type
- Model ID
- Description
- Changes (expandable)
- IP Address (optional column)
- Actions (view details)

**Detail View:**

- Full change log
- Before/After comparison
- Related activities
- User information
- Request metadata
- JSON properties display

### 6. Cleanup and Retention

**Create Command:**

```php
// app/Console/Commands/CleanOldAuditLogs.php
class CleanOldAuditLogs extends Command
{
    protected $signature = 'audit-logs:clean {--days=90}';

    public function handle()
    {
        $days = $this->option('days');

        $deleted = Activity::where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} audit log entries older than {$days} days.");
    }
}
```

**Schedule:**

```php
// In Kernel.php
$schedule->command('audit-logs:clean')->monthly();
```

### 7. Security Considerations

**Protect Audit Logs:**

- Audit logs should be read-only (no edit/delete for non-super-admin)
- Implement soft deletes if absolutely necessary
- Log who views audit logs (meta-logging)
- Encrypt sensitive data in logs
- Restrict access based on permissions

**Middleware:**

```php
Route::middleware(['permission:audit-logs.view'])->group(function () {
    // Audit log routes
});
```

### 8. Configuration

**Verify config/activitylog.php:**

```php
return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 90,
    'default_log_name' => 'default',
    'default_auth_driver' => null,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
];
```

## Acceptance Criteria

✅ All critical models have audit logging enabled
✅ Login attempts are logged
✅ Permission/role changes are logged
✅ Super Admin can view all audit logs
✅ Admin can view relevant audit logs
✅ Audit logs are filterable and searchable
✅ Changes show before/after values
✅ Audit log viewer is user-friendly
✅ Old logs are automatically cleaned up
✅ Audit logs cannot be modified
✅ Export functionality works
✅ Performance is acceptable for large log volumes

## Testing Requirements

- Unit tests for logging functionality
- Feature tests for audit log retrieval
- Permission tests for audit log access
- Performance tests with large datasets
- UI tests for log viewer
- Integration tests with all models

## Estimated Effort

**High Priority:** 2-3 days

**Breakdown:**

- Verification and model updates: 1 day
- Frontend UI development: 1 day
- Testing and refinement: 0.5-1 day

## Dependencies

- Spatie Activity Log package (likely already installed)
- Proper permissions system
- May require export package for CSV/Excel

## Implementation Checklist

- [ ] Verify Spatie Activity Log is installed and configured
- [ ] Add LogsActivity trait to all critical models
- [ ] Implement login attempt logging
- [ ] Create audit log viewer controllers
- [ ] Build frontend UI for audit logs
- [ ] Add filtering and search functionality
- [ ] Implement export feature
- [ ] Create cleanup command
- [ ] Add permissions for audit log access
- [ ] Write comprehensive tests
- [ ] Document audit log usage

## Notes

- Consider real-time audit log monitoring for security team
- Add alerts for suspicious activities
- Consider integration with SIEM tools
- Implement audit log backup and archival
- Add audit log statistics dashboard
- Consider compliance requirements (GDPR/DPA)
- Document what data is logged for privacy compliance
