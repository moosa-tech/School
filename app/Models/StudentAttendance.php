<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentAttendance extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'student_attendance';

    protected $fillable = [
        'school_id',
        'attendance_session_id',
        'student_id',
        'status', // present, absent, late, excused
        'marked_by',
        'marked_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'marked_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(AttendanceChangeLog::class);
    }
}
