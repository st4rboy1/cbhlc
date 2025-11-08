<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('Enrollment Application Submitted Successfully')
            ->greeting('Hello '.$this->enrollment->guardian->name.'!')
            ->line('Thank you for submitting an enrollment application for '.$student->full_name.'.')
            ->line('Application Details:')
            ->line('Student: '.$student->full_name)
            ->line('Grade Level: '.$this->enrollment->grade_level->label())
            ->line('School Year: '.$this->enrollment->schoolYear->name)
            ->line('Application ID: '.$this->enrollment->enrollment_id)
            ->line('Your application is currently being reviewed by our registrar team.')
            ->line('You will receive a notification once your application has been reviewed.')
            ->action('View Application Status', route('guardian.enrollments.show', $this->enrollment))
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $student = $this->enrollment->student;

        return [
            'enrollment_id' => $this->enrollment->id,
            'application_id' => $this->enrollment->enrollment_id,
            'student_id' => $this->enrollment->student_id,
            'student_name' => $student->full_name,
            'grade_level' => $this->enrollment->grade_level->label(),
            'school_year' => $this->enrollment->schoolYear->name,
            'status' => 'submitted',
            'message' => 'Enrollment application submitted for '.$student->full_name,
            'details' => [
                'Student' => $student->full_name,
                'Grade Level' => $this->enrollment->grade_level->label(),
                'School Year' => $this->enrollment->schoolYear->name,
                'Application ID' => $this->enrollment->enrollment_id,
            ],
            'action_url' => route('guardian.enrollments.show', $this->enrollment),
        ];
    }
}
