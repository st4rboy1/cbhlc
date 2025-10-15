<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the guardian profile associated with this user
     */
    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * Get the dashboard route based on user role
     */
    public function getDashboardRoute(): string
    {
        if ($this->hasRole('super_admin')) {
            return 'super-admin.dashboard';
        } elseif ($this->hasRole('administrator')) {
            return 'admin.dashboard';
        } elseif ($this->hasRole('registrar')) {
            return 'registrar.dashboard';
        } elseif ($this->hasRole('guardian')) {
            return 'guardian.dashboard';
        } elseif ($this->hasRole('student')) {
            return 'student.dashboard';
        }

        // Default to home page if user has no role (shouldn't happen in production)
        return 'home';
    }
}
