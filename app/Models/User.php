<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Check if user is a guardian
     */
    public function isGuardian(): bool
    {
        return $this->hasRole('guardian');
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
        } elseif ($this->hasRole('guardian')) {
            return 'guardian.dashboard';
        } elseif ($this->hasRole('student')) {
            return 'student.dashboard';
        }

        // Default to home page if user has no role (shouldn't happen in production)
        return 'home';
    }

    /**
     * Get the children (students) associated with this guardian
     * Only available for users with guardian role
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_students', 'guardian_id', 'student_id')
            ->withPivot(['relationship_type', 'is_primary_contact'])
            ->withTimestamps();
    }

    /**
     * Get enrollments for this guardian's children
     * Only available for users with guardian role
     */
    public function childrenEnrollments()
    {
        return Enrollment::whereIn('student_id', $this->children()->pluck('students.id'));
    }

}
