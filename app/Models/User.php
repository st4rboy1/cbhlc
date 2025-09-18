<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is an administrator (including super admin)
     */
    public function isAdministrator(): bool
    {
        return $this->hasAnyRole(['super_admin', 'administrator']);
    }

    /**
     * Check if user is a registrar
     */
    public function isRegistrar(): bool
    {
        return $this->hasRole('registrar');
    }

    /**
     * Check if user is a parent
     */
    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Get the dashboard route based on user role
     */
    public function getDashboardRoute(): string
    {
        if ($this->hasAnyRole(['super_admin', 'administrator'])) {
            return 'admin.dashboard';
        } elseif ($this->hasRole('registrar')) {
            return 'registrar.dashboard';
        } elseif ($this->hasRole('parent')) {
            return 'parent.dashboard';
        } elseif ($this->hasRole('student')) {
            return 'student.dashboard';
        }

        return 'dashboard';
    }
}
