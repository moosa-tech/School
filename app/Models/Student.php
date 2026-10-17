<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use BelongsToSchool, HasFactory;

    protected $hidden = [
        'qr_token',
        'qr_token_hash',
    ];

    protected $fillable = [
        'school_id',
        'user_id',
        'admission_number',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'admission_date',
        'status', // active, pending, transferred, withdrawn, graduated
        'photo_path',
        'address',
        'emergency_phone',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'qr_token' => 'encrypted',
            'qr_token_issued_at' => 'datetime',
            'qr_token_revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (blank($student->qr_token_hash)) {
                $student->setNewQrToken();
            }
        });
    }

    /**
     * Helper for student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function issueQrToken(): string
    {
        $this->setNewQrToken();
        $this->save();

        return $this->qr_token;
    }

    public function revokeQrToken(): void
    {
        $this->forceFill([
            'qr_token' => null,
            'qr_token_hash' => null,
            'qr_token_revoked_at' => now(),
        ])->save();
    }

    public function hasActiveQrToken(): bool
    {
        return filled($this->qr_token_hash)
            && $this->qr_token_revoked_at === null
            && filled($this->qr_token);
    }

    private function setNewQrToken(): void
    {
        $token = bin2hex(random_bytes(32));

        $this->forceFill([
            'qr_token' => $token,
            'qr_token_hash' => hash('sha256', $token),
            'qr_token_issued_at' => now(),
            'qr_token_revoked_at' => null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot('is_primary', 'receives_notifications')
            ->withTimestamps();
    }

    public function primaryGuardian()
    {
        return $this->guardians()->wherePivot('is_primary', true)->first();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->latestOfMany();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function feeAssignments(): HasMany
    {
        return $this->hasMany(StudentFeeAssignment::class);
    }

    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function feeAdjustments(): HasMany
    {
        return $this->hasMany(FeeAdjustment::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }
}
