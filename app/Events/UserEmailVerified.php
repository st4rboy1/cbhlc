<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEmailVerified
{
    use Dispatchable;
    use SerializesModels;

    public User $user;

    public string $ipAddress;

    public string $userAgent;

    public \DateTime $verifiedAt;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $ipAddress, string $userAgent)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->verifiedAt = now();
    }

    /**
     * Calculate time to verify in human-readable format
     */
    public function getTimeToVerify(): string
    {
        $registrationDate = $this->user->created_at;
        $verificationDate = $this->verifiedAt;

        $diff = $registrationDate->diff($verificationDate);

        if ($diff->days > 0) {
            return $diff->days.' day'.($diff->days > 1 ? 's' : '').' '.$diff->h.' hour'.($diff->h != 1 ? 's' : '');
        }

        if ($diff->h > 0) {
            return $diff->h.' hour'.($diff->h > 1 ? 's' : '');
        }

        return $diff->i.' minute'.($diff->i > 1 ? 's' : '');
    }
}
