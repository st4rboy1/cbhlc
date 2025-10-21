<?php

namespace App\Console\Commands;

use App\Enums\EnrollmentStatus;
use App\Mail\PaymentOverdueNotice;
use App\Mail\PaymentReminder;
use App\Models\Enrollment;
use App\Models\PaymentReminder as PaymentReminderModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature = 'enrollment:send-payment-reminders {--dry-run : Run without sending emails}';

    protected $description = 'Send payment reminder emails for upcoming and overdue payments';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if (! $this->isReminderEnabled()) {
            $this->info('Payment reminders are disabled in settings.');

            return 0;
        }

        $today = now()->startOfDay();

        // Find enrollments with outstanding balance
        $enrollments = Enrollment::with(['student', 'guardian.user'])
            ->where('status', EnrollmentStatus::ENROLLED)
            ->where('balance_cents', '>', 0)
            ->whereNotNull('payment_due_date')
            ->get();

        $this->info("Found {$enrollments->count()} enrollments with outstanding balance.");

        $sent = 0;

        foreach ($enrollments as $enrollment) {
            $dueDate = $enrollment->payment_due_date;
            $daysUntilDue = (int) $today->diffInDays($dueDate, false);

            $reminderType = $this->determineReminderType($daysUntilDue);

            if (! $reminderType) {
                continue; // No reminder needed for this enrollment today
            }

            // Check if reminder already sent
            if ($this->reminderAlreadySent($enrollment->id, $reminderType)) {
                continue;
            }

            if ($dryRun) {
                $this->line("Would send {$reminderType} reminder for enrollment {$enrollment->enrollment_id}");
                $sent++;
                continue;
            }

            // Send reminder
            $this->sendReminder($enrollment, $reminderType, $daysUntilDue);
            $sent++;
        }

        $this->info("Sent {$sent} payment reminders.");

        return 0;
    }

    private function isReminderEnabled(): bool
    {
        return DB::table('settings')
            ->where('key', 'payment_reminders_enabled')
            ->value('value') !== '0';
    }

    private function determineReminderType(int $daysUntilDue): ?string
    {
        return match (true) {
            $daysUntilDue === 7 => 'upcoming_7days',
            $daysUntilDue === 3 => 'upcoming_3days',
            $daysUntilDue === 1 => 'upcoming_1day',
            $daysUntilDue === 0 => 'overdue',
            $daysUntilDue === -7 => 'overdue_7days',
            $daysUntilDue === -30 => 'overdue_30days',
            default => null,
        };
    }

    private function reminderAlreadySent(int $enrollmentId, string $reminderType): bool
    {
        return PaymentReminderModel::where('enrollment_id', $enrollmentId)
            ->where('reminder_type', $reminderType)
            ->whereDate('sent_at', today())
            ->exists();
    }

    private function sendReminder(Enrollment $enrollment, string $reminderType, int $daysUntilDue): void
    {
        $guardianEmail = $enrollment->guardian?->user?->email;

        if (! $guardianEmail) {
            $this->warn("No email for enrollment {$enrollment->enrollment_id}");

            return;
        }

        if (str_contains($reminderType, 'overdue')) {
            Mail::to($guardianEmail)->queue(new PaymentOverdueNotice($enrollment, abs($daysUntilDue)));
        } else {
            Mail::to($guardianEmail)->queue(new PaymentReminder($enrollment, $daysUntilDue));
        }

        // Record reminder sent
        PaymentReminderModel::create([
            'enrollment_id' => $enrollment->id,
            'reminder_type' => $reminderType,
            'sent_at' => now(),
        ]);

        $this->line("Sent {$reminderType} reminder to {$guardianEmail}");
    }
}
