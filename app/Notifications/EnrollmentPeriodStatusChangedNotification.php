<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentPeriodStatusChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->shouldReceiveNotification('enrollment_period_changed', 'mail')) {
            $channels[] = 'mail';
        }

        if ($notifiable->shouldReceiveNotification('enrollment_period_changed', 'database')) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Enrollment Period Status Update')
            ->line('The enrollment period statuses have been automatically updated:');

        if ($this->data['activated'] > 0) {
            $mail->line("✓ {$this->data['activated']} period(s) activated");
        }

        if ($this->data['closed'] > 0) {
            $mail->line("✓ {$this->data['closed']} period(s) closed");
        }

        return $mail
            ->action('View Enrollment Periods', route('super-admin.enrollment-periods.index'))
            ->line('No action is required. This is an automated notification.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = 'Enrollment period statuses have been updated.';
        $details = [];

        if ($this->data['activated'] > 0) {
            $details['Activated Periods'] = $this->data['activated'];
        }

        if ($this->data['closed'] > 0) {
            $details['Closed Periods'] = $this->data['closed'];
        }

        return [
            'message' => $message,
            'details' => $details,
            'action_url' => route('super-admin.enrollment-periods.index'),
        ];
    }
}
