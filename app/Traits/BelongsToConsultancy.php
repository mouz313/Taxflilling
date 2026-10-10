<?php

namespace App\Traits;

use App\Models\Consultancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToConsultancy
{
    /**
     * Boot the BelongsToConsultancy trait.
     * Automatically applies Global Scope to restrict queries to current user's consultancy,
     * and sets consultancy_id on creating new models.
     */
    protected static function bootBelongsToConsultancy(): void
    {
        // Global Scope: Restrict query results to current user's consultancy
        static::addGlobalScope('consultancy', function (Builder $builder) {
            if (auth()->check() && auth()->user()->consultancy_id) {
                $builder->where($builder->getModel()->getTable() . '.consultancy_id', auth()->user()->consultancy_id);
            }
        });

        // Model Event: Auto-assign consultancy_id when creating new records
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->consultancy_id && empty($model->consultancy_id)) {
                $model->consultancy_id = auth()->user()->consultancy_id;
            }
        });
    }

    /**
     * Relationship to Consultancy
     */
    public function consultancy(): BelongsTo
    {
        return $this->belongsTo(Consultancy::class);
    }
}
