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

class EnrollmentApproved extends Mailable implements ShouldQueue
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
            subject: 'Enrollment Approved - Welcome to CBHLC!',
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
        $tuitionFee = number_format($this->enrollment->tuition_fee / 100, 2);
        $totalAmount = number_format($this->enrollment->total_amount / 100, 2);

        return new Content(
            markdown: 'emails.enrollment.approved',
            with: [
                'studentName' => $this->enrollment->student->full_name,
                'enrollmentId' => $this->enrollment->enrollment_id,
                'gradeLevel' => $this->enrollment->grade_level_label,
                'schoolYear' => $this->enrollment->school_year,
                'approvedAt' => $this->enrollment->approved_at?->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A'),
                'tuitionFee' => $tuitionFee,
                'totalAmount' => $totalAmount,
                'paymentDueDate' => $this->enrollment->payment_due_date?->format('F j, Y'),
                'nextSteps' => [
                    'Complete payment of enrollment fees',
                    'Submit any remaining required documents',
                    'Attend orientation scheduled for new students',
                    'Purchase required school supplies and uniforms',
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
