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

class EnrollmentSubmitted extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Enrollment $enrollment
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enrollment Application Received - '.$this->enrollment->student->full_name,
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
            markdown: 'emails.enrollment.submitted',
            with: [
                'studentName' => $this->enrollment->student->full_name,
                'enrollmentId' => $this->enrollment->enrollment_id,
                'gradeLevel' => $this->enrollment->grade_level_label,
                'schoolYear' => $this->enrollment->schoolYear->name,
                'submittedAt' => $this->enrollment->created_at->format('F j, Y g:i A'),
                'nextSteps' => [
                    'Your application will be reviewed by our admissions team',
                    'You will receive an email once your application is processed',
                    'Processing typically takes 2-3 business days',
                    'Ensure all required documents are submitted',
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
