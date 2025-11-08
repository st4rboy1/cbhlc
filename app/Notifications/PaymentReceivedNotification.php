<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Payment $payment
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
        $invoice = $this->payment->invoice;
        $amount = (float) $this->payment->amount;

        $mailMessage = (new MailMessage)
            ->subject('Payment Received')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We have received your payment. Thank you!')
            ->line('Payment Details:')
            ->line('Reference Number: '.$this->payment->reference_number)
            ->line('Amount: ₱'.number_format($amount, 2))
            ->line('Payment Method: '.ucfirst($this->payment->payment_method->value))
            ->line('Payment Date: '.$this->payment->payment_date->format('F d, Y'))
            ->line('Invoice: '.($invoice ? $invoice->invoice_number : 'N/A'));

        // Add action button to view invoice if available
        if ($invoice) {
            $mailMessage->action('View Invoice', route('guardian.invoices.show', $invoice));
        }

        return $mailMessage->line('A receipt will be generated shortly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $amount = (float) $this->payment->amount;
        $invoice = $this->payment->invoice;

        return [
            'payment_id' => $this->payment->id,
            'invoice_id' => $invoice?->id,
            'reference_number' => $this->payment->reference_number,
            'amount' => $amount,
            'payment_method' => $this->payment->payment_method->value,
            'payment_date' => $this->payment->payment_date,
            'message' => 'Payment of ₱'.number_format($amount, 2).' received',
            'details' => [
                'Reference Number' => $this->payment->reference_number,
                'Amount' => '₱'.number_format($amount, 2),
                'Payment Method' => ucfirst($this->payment->payment_method->value),
                'Payment Date' => $this->payment->payment_date->format('F d, Y'),
                'Invoice' => ($invoice ? $invoice->invoice_number : 'N/A'),
            ],
            'action_url' => $invoice ? route('guardian.invoices.show', $invoice) : null,
        ];
    }
}
