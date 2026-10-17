<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class AnyFilter
{
    /**
     * Apply each populated filter as an alternative to the others.
     *
     * @param  list<(callable(Builder): void)|null>  $filters
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        $filters = array_values(array_filter($filters));

        if ($filters === []) {
            return $query;
        }

        return $query->where(function (Builder $group) use ($filters): void {
            foreach ($filters as $index => $filter) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $group->{$method}(fn (Builder $condition) => $filter($condition));
            }
        });
    }
}
