<?php namespace Prometa\Sleek\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * Adds a method to the user that automatically adds sorting based on query string parameters.
 */
trait CanAutoSort
{
    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeAutoSort(Builder $query): Builder
    {
        $sortBy = request('sort-by');
        $sortDirection = request('sort-direction', 'desc');

        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }
}
