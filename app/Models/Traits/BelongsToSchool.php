<?php

namespace App\Models\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    /**
     * Boot the BelongsToSchool trait for an Eloquent model.
     */
    protected static function bootBelongsToSchool(): void
    {
        static::creating(function ($model) {
            if (empty($model->school_id) && session()->has('current_school_id')) {
                $model->school_id = session('current_school_id');
            }
        });

        static::addGlobalScope('school', function (Builder $builder) {
            if (session()->has('current_school_id')) {
                $builder->where($builder->getModel()->getTable().'.school_id', session('current_school_id'));
            }
        });
    }

    /**
     * Scope a query to only include records of a given school.
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->withoutGlobalScope('school')->where($this->getTable().'.school_id', $schoolId);
    }

    /**
     * Get the school that owns the model.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
