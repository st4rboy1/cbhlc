<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
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
        $amount = $this->payment->amount_cents / 100;

        return (new MailMessage)
            ->subject('Payment Received')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We have received your payment. Thank you!')
            ->line('Payment Details:')
            ->line('Reference Number: '.$this->payment->reference_number)
            ->line('Amount: ₱'.number_format($amount, 2))
            ->line('Payment Method: '.ucfirst($this->payment->payment_method->value))
            ->line('Payment Date: '.$this->payment->payment_date->format('F d, Y'))
            ->line('Invoice: '.($invoice ? $invoice->invoice_number : 'N/A'))
            ->action('View Payment', route('guardian.payments.show', $this->payment))
            ->line('A receipt will be generated shortly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'reference_number' => $this->payment->reference_number,
            'amount' => $this->payment->amount_cents / 100,
            'payment_method' => $this->payment->payment_method->value,
            'payment_date' => $this->payment->payment_date,
            'message' => 'Payment of ₱'.number_format($this->payment->amount_cents / 100, 2).' received',
        ];
    }
}
