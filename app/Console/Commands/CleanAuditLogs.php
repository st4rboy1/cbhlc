<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class CleanAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit-logs:clean
                            {--days=90 : Number of days to keep audit logs}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete audit logs older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        if ($days <= 0) {
            $this->error('Days must be a positive integer.');

            return self::FAILURE;
        }

        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning audit logs older than {$days} days (before {$cutoffDate->toDateString()})...");

        // Get count of logs to delete
        $count = Activity::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('No audit logs to clean.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("DRY RUN: Would delete {$count} audit log(s).");
            $this->info('Run without --dry-run to actually delete the logs.');

            return self::SUCCESS;
        }

        // Delete old logs
        $deleted = Activity::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Successfully deleted {$deleted} audit log(s).");

        return self::SUCCESS;
    }
}
