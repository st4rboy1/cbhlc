<?php

namespace App\Console\Commands;

use App\Models\EnrollmentPeriod;
use App\Models\User;
use App\Notifications\EnrollmentPeriodStatusChangedNotification;
use Illuminate\Console\Command;

class UpdateEnrollmentPeriodStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment-periods:update-status
                          {--dry-run : Preview changes without applying}
                          {--notify : Send notifications to administrators}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update enrollment period statuses based on dates';

    /**
     * Execute the console command.
     */
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

            if ($shouldNotify && ! $isDryRun) {
                $this->sendNotifications($activated, $closed);
            }
        } else {
            $this->info('No status changes needed.');
        }

        return Command::SUCCESS;
    }

    /**
     * Activate upcoming periods that have reached their start date.
     */
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
            $toActivate->each(fn ($p) => $this->line("  - {$p->school_year}"));

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

    /**
     * Close active periods that have passed their end date.
     */
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
            $toClose->each(fn ($p) => $this->line("  - {$p->school_year}"));

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

    /**
     * Send notifications to administrators about status changes.
     */
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