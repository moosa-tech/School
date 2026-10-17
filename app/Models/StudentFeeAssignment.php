<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeAssignment extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_structure_id',
        'amount_override',
        'discount_amount',
        'starts_on',
        'ends_on',
    ];

    protected function casts(): array
    {
        return [
            'amount_override' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function effectiveAmount(): float
    {
        $base = $this->amount_override ?? $this->feeStructure->amount;

        return max(0, (float) $base - (float) $this->discount_amount);
    }
}
