<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $user
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
        $roles = $this->user->roles->pluck('name')->map(fn ($role) => ucwords(str_replace('_', ' ', $role)))->implode(', ');

        return (new MailMessage)
            ->subject('New User Registration')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new user has registered in the system.')
            ->line('User Details:')
            ->line('Name: '.$this->user->name)
            ->line('Email: '.$this->user->email)
            ->line('Role(s): '.$roles)
            ->line('Registration Date: '.$this->user->created_at->format('F d, Y h:i A'))
            ->action('View User', route('super-admin.users.show', $this->user))
            ->line('Please review this new user registration.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roles = $this->user->roles->pluck('name')->map(fn ($role) => ucwords(str_replace('_', ' ', $role)))->implode(', ');

        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'roles' => $this->user->roles->pluck('name')->toArray(),
            'registered_at' => $this->user->created_at,
            'message' => 'New user '.$this->user->name.' has registered',
            'details' => [
                'Name' => $this->user->name,
                'Email' => $this->user->email,
                'Role(s)' => $roles,
                'Registration Date' => $this->user->created_at->format('F d, Y h:i A'),
            ],
            'action_url' => route('super-admin.users.show', $this->user),
        ];
    }
}
