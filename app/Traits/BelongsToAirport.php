<?php

namespace App\Traits;

trait BelongsToAirport
{
    protected static function bootBelongsToAirport()
    {
        static::creating(function ($model) {
            if (!$model->airport_id && auth()->check()) {
                $model->airport_id = auth()->user()->airport_id;
            }
        });

        if (!auth()->user()?->hasRole('Super Admin')) {
            static::addGlobalScope('airport', function ($query) {
                $query->where('airport_id', auth()->user()->airport_id);
            });
        }
    }

    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }
}