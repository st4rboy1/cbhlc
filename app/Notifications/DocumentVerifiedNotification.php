<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentVerifiedNotification extends Notification implements ShouldQueue
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
            ->subject('Document Verified')
            ->line('Your uploaded document has been verified.')
            ->line('Document Type: '.$this->document->document_type->label())
            ->line('Student: '.$this->document->student->full_name)
            ->line('Verified Date: '.$this->document->verified_at->format('F d, Y'))
            ->action('View Documents', route('guardian.students.documents.index', $this->document->student))
            ->line('Thank you for your submission!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Document Verified: '.$this->document->document_type->label().' for '.$this->document->student->full_name,
            'document_id' => $this->document->id,
            'document_type' => $this->document->document_type,
            'student_id' => $this->document->student_id,
            'student_name' => $this->document->student->full_name,
            'verified_at' => $this->document->verified_at,
            'details' => [
                'Document Type' => $this->document->document_type->label(),
                'Student' => $this->document->student->full_name,
                'Verified Date' => $this->document->verified_at->format('F d, Y'),
            ],
            'action_url' => route('guardian.students.documents.index', $this->document->student),
        ];
    }
}
