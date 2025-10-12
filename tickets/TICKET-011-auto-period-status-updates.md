# Ticket #011: Automatic Period Status Updates

**Epic:** [EPIC-002 Enrollment Period Management](./EPIC-002-enrollment-period-management.md)

**Type:** Story
**Priority:** Medium
**Estimated Effort:** 0.5 day
**Assignee:** TBD

## Description

Implement scheduled job to automatically update enrollment period statuses based on dates, transitioning periods from upcoming to active to closed without manual intervention.

## Acceptance Criteria

- [ ] Artisan command created: `enrollment-periods:update-status`
- [ ] Command activates upcoming periods when start date is reached
- [ ] Command closes active periods when end date is passed
- [ ] Command scheduled to run daily
- [ ] Activity logged for status changes
- [ ] Optional notifications sent to admins
- [ ] Command can be run manually
- [ ] Dry-run mode for testing

## Implementation Details

### Console Command

`app/Console/Commands/UpdateEnrollmentPeriodStatus.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\EnrollmentPeriod;
use Illuminate\Console\Command;

class UpdateEnrollmentPeriodStatus extends Command
{
    protected $signature = 'enrollment-periods:update-status
                          {--dry-run : Preview changes without applying}
                          {--notify : Send notifications to administrators}';

    protected $description = 'Automatically update enrollment period statuses based on dates';

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

    protected function sendNotifications(int $activated, int $closed): void
    {
        $admins = User::role(['super_admin', 'administrator'])->get();

        $message = "Enrollment period status update:\n";
        if ($activated > 0) {
            $message .= "- {$activated} period(s) activated\n";
        }
        if ($closed > 0) {
            $message .= "- {$closed} period(s) closed\n";
        }

        foreach ($admins as $admin) {
            $admin->notify(new EnrollmentPeriodStatusChangedNotification([
                'activated' => $activated,
                'closed' => $closed,
            ]));
        }

        $this->info('Notifications sent to administrators.');
    }
}
```

### Schedule Command

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Run daily at 12:00 AM
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

### Notification

`app/Notifications/EnrollmentPeriodStatusChangedNotification.php`

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

### Manual Execution

Admins can run the command manually:

```bash
# Preview changes
php artisan enrollment-periods:update-status --dry-run

# Apply changes
php artisan enrollment-periods:update-status

# Apply changes and notify
php artisan enrollment-periods:update-status --notify
```

### Add to Super Admin UI

Add button to manually trigger status update:
`resources/js/pages/super-admin/enrollment-periods/index.tsx`

```tsx
<Button
    onClick={() => {
        router.post('/super-admin/enrollment-periods/update-status');
    }}
>
    Update Status Now
</Button>
```

Backend route:

```php
Route::post('/super-admin/enrollment-periods/update-status', function () {
    Artisan::call('enrollment-periods:update-status', ['--notify' => true]);

    return back()->with('success', 'Period statuses updated successfully.');
})->name('super-admin.enrollment-periods.update-status');
```

## Testing Requirements

- [ ] Unit test: activatePeriods() logic
- [ ] Unit test: closePeriods() logic
- [ ] Feature test: command runs successfully
- [ ] Feature test: dry-run mode
- [ ] Feature test: notification sending
- [ ] Feature test: only one active period at a time
- [ ] Feature test: activity logging
- [ ] Integration test: scheduled job execution
- [ ] Manual test: run command via artisan

## Dependencies

- [TICKET-007](./TICKET-007-enrollment-period-model-migration.md) - EnrollmentPeriod model
- Task scheduling enabled on server
- Activity log package
- Notification system

## Notes

- Ensure server has cron job for Laravel scheduler
- Consider timezone handling for global deployments
- Add monitoring/alerting for command failures
- Log all status changes for audit trail
- Consider grace period before auto-closing
