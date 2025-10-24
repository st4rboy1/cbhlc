<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnrollmentForReviewNotification extends Notification
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
            ->subject('New Enrollment Application Requires Review')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new enrollment application has been submitted and requires your review.')
            ->line('Application Details:')
            ->line('Student: '.$student->full_name)
            ->line('Grade Level: '.$this->enrollment->grade_level)
            ->line('School Year: '.$this->enrollment->schoolYear->name)
            ->line('Application ID: '.$this->enrollment->enrollment_id)
            ->line('Submitted by: '.$this->enrollment->guardian->name)
            ->line('Submission Date: '.$this->enrollment->created_at->format('F d, Y'))
            ->action('Review Application', route('registrar.enrollments.show', $this->enrollment))
            ->line('Please review and process this application at your earliest convenience.');
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
            'grade_level' => $this->enrollment->grade_level,
            'school_year' => $this->enrollment->schoolYear->name,
            'guardian_name' => $this->enrollment->guardian->name,
            'submitted_at' => $this->enrollment->created_at,
            'status' => 'pending_review',
            'message' => 'New enrollment application from '.$this->enrollment->guardian->name.' requires review',
        ];
    }
}
