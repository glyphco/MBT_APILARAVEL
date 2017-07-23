<?php

namespace App\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EventCurrentScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {

        $builder->where(function ($query) {
            $query
            //Start in date range
            ->whereDate('UTC_start', '>=', Carbon::now()->subHours(5)->toDateTimeString())
            //End in date range
                ->orWhereDate('UTC_end', '>=', Carbon::now()->subHours(5)->toDateTimeString());
        });
    }

}
