<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Enrollment $enrollment,
        public ?string $remarks = null
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
        $mail = (new MailMessage)
            ->subject('Enrollment Application Approved - '.$student->full_name)
            ->greeting('Congratulations, '.$this->enrollment->guardian->name.'!')
            ->line('We are pleased to inform you that the enrollment application for '.$student->full_name.' has been approved.')
            ->line('Enrollment Details:')
            ->line('Student: '.$student->full_name)
            ->line('Grade Level: '.$this->enrollment->grade_level->label())
            ->line('School Year: '.$this->enrollment->schoolYear->name)
            ->line('Application ID: '.$this->enrollment->enrollment_id)
            ->line('Approval Date: '.$this->enrollment->approved_at->format('F d, Y'));

        if ($this->remarks) {
            $mail->line('Remarks from Registrar:')
                ->line($this->remarks);
        }

        $mail->line('Next Steps:')
            ->line('1. Review and process payment for enrollment fees')
            ->line('2. Complete any remaining document requirements')
            ->line('3. Wait for school opening announcements')
            ->action('View Billing Information', route('guardian.billing.show', $this->enrollment))
            ->line('Welcome to our school community!');

        return $mail;
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
            'approved_at' => $this->enrollment->approved_at,
            'status' => 'approved',
            'remarks' => $this->remarks,
            'message' => 'Enrollment application approved for '.$this->enrollment->student->full_name,
        ];
    }
}
