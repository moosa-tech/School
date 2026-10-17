<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'status',
        'email_verified_at',
        'last_login_at',
        'must_change_password',
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
            'is_super_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Schools where this user is a member.
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_user')
            ->withPivot('role', 'status', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Check if user is platform Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Get the role for a specific school.
     */
    public function schoolRole(?int $schoolId = null): ?string
    {
        $schoolId = $schoolId ?? session('current_school_id');
        if (! $schoolId) {
            return null;
        }

        $membership = $this->schools()->where('school_id', $schoolId)->first();

        return $membership ? $membership->pivot->role : null;
    }

    /**
     * Check if user has a specific role in a school.
     */
    public function hasSchoolRole(string|array $roles, ?int $schoolId = null): bool
    {
        $currentRole = $this->schoolRole($schoolId);
        if (! $currentRole) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }

        return $currentRole === $roles;
    }

    /**
     * Check if user is active member of school.
     */
    public function isActiveInSchool(?int $schoolId = null): bool
    {
        $schoolId = $schoolId ?? session('current_school_id');
        if (! $schoolId) {
            return false;
        }

        return $this->schools()
            ->where('school_id', $schoolId)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Get employee record if linked.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get student record if linked.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get guardian record if linked.
     */
    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * Audit logs triggered by this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id');
    }
}
