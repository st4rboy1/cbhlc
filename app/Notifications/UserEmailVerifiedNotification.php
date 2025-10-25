<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserEmailVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $user;

    public string $timeToVerify;

    public \DateTime $verifiedAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, string $timeToVerify, \DateTime $verifiedAt)
    {
        $this->user = $user;
        $this->timeToVerify = $timeToVerify;
        $this->verifiedAt = $verifiedAt;
    }

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
        $registrationDate = $this->user->created_at->format('M d, Y, g:i A');
        $verificationDate = $this->verifiedAt->format('M d, Y, g:i A');

        $message = (new MailMessage)
            ->subject('User Email Verified - '.$this->user->name)
            ->greeting('✅ User Email Verified')
            ->line($this->user->name.' has successfully verified their email address.')
            ->line('**User Details:**')
            ->line('• Name: '.$this->user->name)
            ->line('• Email: '.$this->user->email)
            ->line('• Registered: '.$registrationDate)
            ->line('• Email Verified: '.$verificationDate)
            ->line('• Time to Verify: '.$this->timeToVerify)
            ->line('• Account Status: Active')
            ->line('')
            ->line('The user can now fully access the system and may begin submitting enrollment applications.')
            ->action('View User Profile', route('super-admin.users.show', $this->user->id));

        // Add note if verification took longer than usual
        $hoursToVerify = $this->user->created_at->diffInHours($this->verifiedAt);
        if ($hoursToVerify > 48) {
            $message->line('')
                ->line('⚠️ Note: This user took longer than usual to verify. They may need additional support or guidance.');
        }

        return $message;
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'user_email_verified',
            'title' => 'User Email Verified',
            'message' => $this->user->name.' has verified their email address.',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'registration_date' => $this->user->created_at->toISOString(),
            'verification_date' => $this->verifiedAt->format('c'),
            'time_to_verify' => $this->timeToVerify,
            'account_status' => 'active',
            'action_url' => route('super-admin.users.show', $this->user->id),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
