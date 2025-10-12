# PR #011: Automatic Period Status Updates

## Related Ticket

[TICKET-011: Automatic Period Status Updates](./TICKET-011-auto-period-status-updates.md)

## Epic

[EPIC-002: Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

## Description

This PR implements automated enrollment period status management through a scheduled command that activates upcoming periods when their start date arrives and closes active periods when their end date passes, with optional admin notifications.

## Changes Made

### Console Command

- ✅ Created `app/Console/Commands/UpdateEnrollmentPeriodStatus.php`
- ✅ Implemented `activatePeriods()` method
- ✅ Implemented `closePeriods()` method
- ✅ Added `--dry-run` flag for safe testing
- ✅ Added `--notify` flag for admin notifications
- ✅ Activity logging for automated actions

### Task Scheduling

- ✅ Scheduled command in `app/Console/Kernel.php`
- ✅ Daily execution at midnight (configurable)
- ✅ Success/failure logging

### Notifications

- ✅ Created `EnrollmentPeriodStatusChangedNotification`
- ✅ Email template for admins
- ✅ Database notification

### Manual Trigger

- ✅ Added route for manual execution via Super Admin UI
- ✅ Added button to UI

## Type of Change

- [x] New feature (automation)
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation update

## Testing Checklist

### Command Tests

- [ ] Dry-run mode shows changes without applying
- [ ] Activates upcoming periods when start date reached
- [ ] Closes active periods when end date passed
- [ ] Only one period active at a time after activation
- [ ] Activity logged for automated actions
- [ ] Notifications sent when --notify flag used
- [ ] Returns correct exit codes

### Schedule Tests

- [ ] Command scheduled correctly in Kernel
- [ ] Runs at specified time (midnight)
- [ ] Success logged
- [ ] Failure logged

### Integration Tests

- [ ] Period transitions work correctly
- [ ] Enrollments blocked after auto-close
- [ ] Enrollments allowed after auto-activate
- [ ] Manual trigger works from UI

## Verification Steps

```bash
# Test dry-run
./vendor/bin/sail artisan enrollment-periods:update-status --dry-run

# Test actual execution
./vendor/bin/sail artisan enrollment-periods:update-status

# Test with notifications
./vendor/bin/sail artisan enrollment-periods:update-status --notify

# Test scheduling
./vendor/bin/sail artisan schedule:list
# Should show: enrollment-periods:update-status

# Run scheduled tasks manually
./vendor/bin/sail artisan schedule:run

# Test scenarios:

# Scenario 1: Activate upcoming period
# 1. Create period with start_date = today, status = upcoming
# 2. Run command
# 3. Verify status changed to active
# 4. Verify previous active period closed
# 5. Check activity log

# Scenario 2: Close active period
# 1. Create period with end_date = yesterday, status = active
# 2. Run command
# 3. Verify status changed to closed
# 4. Check activity log

# Scenario 3: No changes needed
# 1. All periods have appropriate status
# 2. Run command
# 3. Verify "No status changes needed" message
```

## Command Usage

```bash
# Preview changes without applying
php artisan enrollment-periods:update-status --dry-run

# Apply changes
php artisan enrollment-periods:update-status

# Apply changes and notify admins
php artisan enrollment-periods:update-status --notify
```

## Command Implementation

### Signature

```php
protected $signature = 'enrollment-periods:update-status
                      {--dry-run : Preview changes without applying}
                      {--notify : Send notifications to administrators}';
```

### Handle Method

```php
public function handle()
{
    $isDryRun = $this->option('dry-run');
    $shouldNotify = $this->option('notify');

    $this->info('Checking enrollment periods...');

    $activated = $this->activatePeriods($isDryRun);
    $closed = $this->closePeriods($isDryRun);

    if ($activated > 0 || $closed > 0) {
        $this->info("✓ Activated: {$activated} period(s)");
        $this->info("✓ Closed: {$closed} period(s)");

        if ($shouldNotify && !$isDryRun) {
            $this->sendNotifications($activated, $closed);
        }
    } else {
        $this->info('No status changes needed.');
    }

    return Command::SUCCESS;
}
```

### Activate Periods

```php
protected function activatePeriods(bool $isDryRun): int
{
    $toActivate = EnrollmentPeriod::where('status', 'upcoming')
        ->where('start_date', '<=', now())
        ->get();

    if ($toActivate->isEmpty()) {
        return 0;
    }

    if ($isDryRun) {
        $this->warn('[DRY RUN] Would activate:');
        $toActivate->each(fn($p) => $this->line("  - {$p->school_year}"));
        return $toActivate->count();
    }

    foreach ($toActivate as $period) {
        // Close any currently active periods
        EnrollmentPeriod::where('status', 'active')
            ->update(['status' => 'closed']);

        $period->update(['status' => 'active']);

        activity()
            ->performedOn($period)
            ->withProperties([
                'automated' => true,
                'previous_status' => 'upcoming',
                'new_status' => 'active',
            ])
            ->log('Enrollment period automatically activated');

        $this->info("Activated: {$period->school_year}");
    }

    return $toActivate->count();
}
```

### Close Periods

```php
protected function closePeriods(bool $isDryRun): int
{
    $toClose = EnrollmentPeriod::where('status', 'active')
        ->where('end_date', '<', now())
        ->get();

    if ($toClose->isEmpty()) {
        return 0;
    }

    if ($isDryRun) {
        $this->warn('[DRY RUN] Would close:');
        $toClose->each(fn($p) => $this->line("  - {$p->school_year}"));
        return $toClose->count();
    }

    foreach ($toClose as $period) {
        $period->update(['status' => 'closed']);

        activity()
            ->performedOn($period)
            ->withProperties([
                'automated' => true,
                'previous_status' => 'active',
                'new_status' => 'closed',
            ])
            ->log('Enrollment period automatically closed');

        $this->info("Closed: {$period->school_year}");
    }

    return $toClose->count();
}
```

## Task Scheduling

### Kernel Configuration

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('enrollment-periods:update-status --notify')
        ->daily()
        ->at('00:00')
        ->timezone('Asia/Manila')
        ->onSuccess(function () {
            Log::info('Enrollment period status update completed successfully.');
        })
        ->onFailure(function () {
            Log::error('Enrollment period status update failed.');
        });
}
```

### Cron Configuration

Ensure server has Laravel scheduler running:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Notification

### EnrollmentPeriodStatusChangedNotification

```php
class EnrollmentPeriodStatusChangedNotification extends Notification
{
    public function __construct(
        public array $data
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrollment Period Status Update')
            ->line('The enrollment period statuses have been automatically updated:')
            ->when($this->data['activated'] > 0, function ($mail) {
                return $mail->line("✓ {$this->data['activated']} period(s) activated");
            })
            ->when($this->data['closed'] > 0, function ($mail) {
                return $mail->line("✓ {$this->data['closed']} period(s) closed");
            })
            ->action('View Enrollment Periods', route('super-admin.enrollment-periods.index'))
            ->line('No action is required. This is an automated notification.');
    }

    public function toArray($notifiable): array
    {
        return $this->data;
    }
}
```

## Manual Trigger UI

### Route

```php
Route::post('/super-admin/enrollment-periods/update-status', function () {
    Artisan::call('enrollment-periods:update-status', ['--notify' => true]);

    return back()->with('success', 'Period statuses updated successfully.');
})->name('super-admin.enrollment-periods.update-status');
```

### Button in UI

```tsx
<Button
    variant="outline"
    onClick={() => {
        router.post('/super-admin/enrollment-periods/update-status');
    }}
>
    <RefreshIcon className="mr-2 h-4 w-4" />
    Update Status Now
</Button>
```

## Activity Logging

### Automated Activation

```
Activity: "Enrollment period automatically activated"
Properties: {
  "automated": true,
  "previous_status": "upcoming",
  "new_status": "active"
}
```

### Automated Closure

```
Activity: "Enrollment period automatically closed"
Properties: {
  "automated": true,
  "previous_status": "active",
  "new_status": "closed"
}
```

## Dependencies

- [PR-007](./PR-007-enrollment-period-model-migration.md) - Model must exist
- Task scheduler must be configured on server
- Queue system for notifications (optional)

## Breaking Changes

None

## Deployment Notes

- **Critical:** Ensure Laravel scheduler is running on server (cron job)
- Verify timezone configuration matches school's timezone
- Test dry-run before first production execution
- Monitor logs after deployment

## Post-Merge Checklist

- [ ] Command runs successfully
- [ ] Dry-run mode works
- [ ] Scheduled task configured in cron
- [ ] Notifications sent to admins
- [ ] Activity logged correctly
- [ ] Manual trigger works from UI
- [ ] Timezone configuration correct
- [ ] Success/failure logging works
- [ ] Epic complete! All enrollment period features implemented

## Reviewer Notes

Please verify:

1. Command logic is correct and safe
2. Dry-run mode prevents actual changes
3. Only one period active after execution
4. Activity logging captures automation flag
5. Notifications are appropriate
6. Scheduling configuration is correct
7. Error handling is comprehensive
8. Manual trigger is properly secured (Super Admin only)

## Monitoring

### Success Indicators

- Log entry: "Enrollment period status update completed successfully"
- Activity log entries for status changes
- Admin notifications received

### Failure Indicators

- Log entry: "Enrollment period status update failed"
- Multiple active periods simultaneously
- Enrollments accepted outside active period

### Recommended Monitoring

- Set up alerts for command failures
- Monitor activity log for automated actions
- Review admin notifications regularly
- Check for periods that should have transitioned but didn't

---

**Ticket:** #011
**Estimated Effort:** 0.5 day
**Actual Effort:** _[To be filled after completion]_
**Epic Status:** ✅ COMPLETE - Enrollment Period Management
