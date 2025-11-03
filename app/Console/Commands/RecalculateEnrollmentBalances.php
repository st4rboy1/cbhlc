<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use Illuminate\Console\Command;

class RecalculateEnrollmentBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollments:recalculate-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the balances for all enrollments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating enrollment balances...');

        $enrollments = Enrollment::all();

        $progressBar = $this->output->createProgressBar($enrollments->count());
        $progressBar->start();

        foreach ($enrollments as $enrollment) {
            $enrollment->recalculateFees();
            $enrollment->updatePaymentDetails();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('\nDone.');
    }
}
