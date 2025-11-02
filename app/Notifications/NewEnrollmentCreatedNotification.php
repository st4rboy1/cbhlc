<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnrollmentCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Enrollment $enrollment
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
            ->subject('New Enrollment Created')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new enrollment has been created in the system.')
            ->line('Enrollment Details:')
            ->line('Student: '.$student->full_name)
            ->line('Student ID: '.$student->student_id)
            ->line('Grade Level: '.$this->enrollment->grade_level->label())
            ->line('School Year: '.$this->enrollment->schoolYear->name)
            ->line('Quarter: '.$this->enrollment->quarter->label())
            ->line('Status: '.ucfirst($this->enrollment->status->value))
            ->line('Created on: '.$this->enrollment->created_at->format('F d, Y h:i A'))
            ->action('View Enrollment', route('registrar.enrollments.show', $this->enrollment))
            ->line('Please review this new enrollment.');
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
            'enrollment_number' => $this->enrollment->enrollment_id,
            'student_id' => $this->enrollment->student_id,
            'student_name' => $this->enrollment->student->full_name,
            'grade_level' => $this->enrollment->grade_level->label(),
            'school_year' => $this->enrollment->schoolYear->name,
            'status' => $this->enrollment->status->value,
            'created_at' => $this->enrollment->created_at,
            'message' => 'New enrollment for '.$this->enrollment->student->full_name.' has been created',
        ];
    }
}
