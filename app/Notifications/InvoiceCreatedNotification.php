<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invoice $invoice
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
        $enrollment = $this->invoice->enrollment;
        $student = $enrollment?->student;

        return (new MailMessage)
            ->subject('New Invoice Generated')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new invoice has been generated for your account.')
            ->line('Invoice Details:')
            ->line('Invoice Number: '.$this->invoice->invoice_number)
            ->line('Student: '.($student ? $student->full_name : 'N/A'))
            ->line('Amount: ₱'.number_format((float) $this->invoice->total_amount, 2))
            ->line('Due Date: '.($this->invoice->due_date ? $this->invoice->due_date->format('F d, Y') : 'N/A'))
            ->line('Status: '.ucfirst($this->invoice->status->value))
            ->action('View Invoice', route('guardian.invoices.show', ['invoice' => $this->invoice->id]))
            ->line('Please ensure payment is made before the due date.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $enrollment = $this->invoice->enrollment;
        $student = $enrollment?->student;

        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date,
            'status' => $this->invoice->status->value,
            'message' => 'New invoice '.$this->invoice->invoice_number.' has been generated',
            'details' => [
                'Invoice Number' => $this->invoice->invoice_number,
                'Student' => ($student ? $student->full_name : 'N/A'),
                'Amount' => '₱'.number_format((float) $this->invoice->total_amount, 2),
                'Due Date' => ($this->invoice->due_date ? $this->invoice->due_date->format('F d, Y') : 'N/A'),
                'Status' => ucfirst($this->invoice->status->value),
            ],
            'action_url' => '/guardian/invoices/'.$this->invoice->id,
        ];
    }
}
