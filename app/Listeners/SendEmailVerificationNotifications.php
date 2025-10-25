<?php

namespace App\Listeners;

use App\Events\UserEmailVerified;
use App\Models\User;
use App\Notifications\UserEmailVerifiedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendEmailVerificationNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserEmailVerified $event): void
    {
        // Log the verification event
        \App\Models\EmailVerificationEvent::create([
            'user_id' => $event->user->id,
            'verified_at' => $event->verifiedAt,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'time_to_verify_minutes' => $event->user->created_at->diffInMinutes($event->verifiedAt),
        ]);

        // Get all users with super_admin and administrator roles
        $admins = User::role(['super_admin', 'administrator'])->get();

        // Calculate time to verify
        $timeToVerify = $event->getTimeToVerify();

        // Send notification to all admins
        Notification::send(
            $admins,
            new UserEmailVerifiedNotification(
                $event->user,
                $timeToVerify,
                $event->verifiedAt
            )
        );
    }
}
