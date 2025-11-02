<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address - Christian Bible Heritage Learning Center')
            ->greeting('Welcome to CBHLC!')
            ->line('Thank you for registering with Christian Bible Heritage Learning Center.')
            ->line('Please verify your email address to complete your registration and access your guardian account.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not create this account, please ignore this email.')
            ->salutation('Best regards, Christian Bible Heritage Learning Center');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return [
            'message' => 'Please verify your email address to complete your registration.',
            'details' => [
                'Action' => 'Verify your email address to access your account.',
                'Expiry' => 'This verification link will expire in 60 minutes.',
            ],
            'action_url' => $verificationUrl,
        ];
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
