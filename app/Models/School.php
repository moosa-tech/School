<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    use HasFactory;

    public const PORTAL_LABELS = [
        'school_admin' => 'School Admin Portal',
        'teacher' => 'Teacher Portal',
        'accountant' => 'Accountant Portal',
        'staff' => 'Staff Portal',
        'student' => 'Student Portal',
    ];

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'currency',
        'timezone',
        'status', // pending, active, suspended, archived
        'created_by',
        'enabled_portals',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled_portals' => 'array',
        ];
    }

    /**
     * Check whether a named portal is enabled for this school.
     *
     * NULL means all portals are enabled (backward-compatible default).
     * Supported portal keys are defined by PORTAL_LABELS.
     */
    public function isPortalEnabled(string $portal): bool
    {
        if ($this->enabled_portals === null) {
            return true;
        }

        return in_array($portal, $this->enabled_portals, true);
    }

    /**
     * Users associated with this school.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot('role', 'status', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Super admin user who created this school.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Invitations for this school.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(SchoolInvitation::class);
    }

    /**
     * Subscriptions history.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Current active subscription.
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Academic years.
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * Current academic year.
     */
    public function currentAcademicYear(): HasOne
    {
        return $this->hasOne(AcademicYear::class)->where('is_current', true);
    }

    /**
     * Classes.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Students.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Employees / Teachers / Staff.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Fee invoices.
     */
    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    /**
     * Fee payments.
     */
    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Check if the school subscription is in healthy operational state.
     */
    public function hasActiveAccess(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $sub = $this->currentSubscription;
        if (! $sub) {
            return false;
        }

        return in_array($sub->status, ['active', 'trial', 'past_due'], true);
    }
}
