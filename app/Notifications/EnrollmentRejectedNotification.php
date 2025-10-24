<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentRejectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Enrollment $enrollment,
        public string $reason
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->enrollment->student;

        return (new MailMessage)
            ->subject('Enrollment Application Status Update - '.$student->full_name)
            ->greeting('Dear '.$this->enrollment->guardian->name.',')
            ->line('We have reviewed your enrollment application for '.$student->full_name.'.')
            ->line('Application Details:')
            ->line('Student: '.$student->full_name)
            ->line('Grade Level: '.$this->enrollment->grade_level->label())
            ->line('School Year: '.$this->enrollment->schoolYear->name)
            ->line('Application ID: '.$this->enrollment->enrollment_id)
            ->line('Unfortunately, we are unable to approve your application at this time.')
            ->line('Reason for Decision:')
            ->line($this->reason)
            ->line('What You Can Do:')
            ->line('• Review the reason provided above')
            ->line('• Address any missing or incorrect information')
            ->line('• Submit a new application once requirements are met')
            ->line('• Contact our registrar office for clarification')
            ->action('Contact Registrar', url('/contact'))
            ->line('We appreciate your interest in our school and hope to assist you further.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'application_id' => $this->enrollment->enrollment_id,
            'student_id' => $this->enrollment->student_id,
            'student_name' => $this->enrollment->student->full_name,
            'grade_level' => $this->enrollment->grade_level->label(),
            'school_year' => $this->enrollment->schoolYear->name,
            'status' => 'rejected',
            'reason' => $this->reason,
            'message' => 'Enrollment application for '.$this->enrollment->student->full_name.' requires your attention',
        ];
    }
}
