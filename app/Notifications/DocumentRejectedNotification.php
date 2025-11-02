<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Document $document
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
        return (new MailMessage)
            ->subject('Document Rejected')
            ->line('Your uploaded document has been rejected.')
            ->line('Document Type: '.$this->document->document_type->label())
            ->line('Student: '.$this->document->student->full_name)
            ->line('Reason: '.$this->document->rejection_reason)
            ->action('Re-upload Document', route('guardian.students.documents.index', $this->document->student))
            ->line('Please upload a corrected version of the document.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Document Rejected: '.$this->document->document_type->label().' for '.$this->document->student->full_name.' needs resubmission',
            'document_id' => $this->document->id,
            'document_type' => $this->document->document_type,
            'student_id' => $this->document->student_id,
            'student_name' => $this->document->student->full_name,
            'rejection_reason' => $this->document->rejection_reason,
            'rejected_at' => $this->document->verified_at,
        ];
    }
}
