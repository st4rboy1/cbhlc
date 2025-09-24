<?php

namespace App\Mail;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentRejected extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Enrollment $enrollment,
        public string $reason = ''
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enrollment Application Update - '.$this->enrollment->student->full_name,
            from: new Address(config('mail.from.address'), 'CBHLC Admissions'),
            replyTo: [
                new Address(config('mail.from.address'), 'CBHLC Admissions'),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.enrollment.rejected',
            with: [
                'studentName' => $this->enrollment->student->full_name,
                'enrollmentId' => $this->enrollment->enrollment_id,
                'gradeLevel' => $this->enrollment->grade_level_label,
                'schoolYear' => $this->enrollment->school_year,
                'rejectedAt' => $this->enrollment->rejected_at?->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A'),
                'reason' => $this->reason ?: $this->enrollment->remarks,
                'nextSteps' => [
                    'Review the reason for the decision',
                    'Address any missing or incomplete requirements',
                    'Contact our admissions office for clarification',
                    'You may reapply once requirements are met',
                ],
                'contactInfo' => [
                    'phone' => config('school.contact_phone', '+63 123 456 7890'),
                    'email' => config('mail.from.address'),
                    'office_hours' => 'Monday to Friday, 8:00 AM - 5:00 PM',
                ],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
