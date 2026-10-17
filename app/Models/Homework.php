<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homework extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'homework';

    protected $fillable = [
        'school_id',
        'class_subject_id',
        'title',
        'description',
        'assigned_on',
        'due_on',
        'attachment_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_on' => 'date',
            'due_on' => 'date',
        ];
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
