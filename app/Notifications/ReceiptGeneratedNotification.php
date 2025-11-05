<?php

namespace App\Notifications;

use App\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptGeneratedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Receipt $receipt
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
        $payment = $this->receipt->payment;
        $amount = $payment ? $payment->amount_cents / 100 : 0;

        return (new MailMessage)
            ->subject('Receipt Generated')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your payment receipt has been generated.')
            ->line('Receipt Details:')
            ->line('Receipt Number: '.$this->receipt->receipt_number)
            ->line('Amount: ₱'.number_format($amount, 2))
            ->line('Issue Date: '.$this->receipt->created_at->format('F d, Y'))
            ->line('Payment Reference: '.($payment ? $payment->reference_number : 'N/A'))
            ->line('Thank you for your payment!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $payment = $this->receipt->payment;
        $amount = $payment ? $payment->amount_cents / 100 : 0;

        return [
            'receipt_id' => $this->receipt->id,
            'receipt_number' => $this->receipt->receipt_number,
            'amount' => $amount,
            'issue_date' => $this->receipt->created_at,
            'message' => 'Receipt '.$this->receipt->receipt_number.' has been generated',
            'details' => [
                'Receipt Number' => $this->receipt->receipt_number,
                'Amount' => '₱'.number_format($amount, 2),
                'Issue Date' => $this->receipt->created_at->format('F d, Y'),
                'Payment Reference' => ($payment ? $payment->reference_number : 'N/A'),
            ],
            'action_url' => route('guardian.receipts.show', $this->receipt),
        ];
    }
}
