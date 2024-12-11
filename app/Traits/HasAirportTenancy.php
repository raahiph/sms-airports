<?php

namespace App\Traits;

trait HasAirportTenancy
{
    public static function bootHasAirportTenancy()
    {
        static::creating(function ($model) {
            // Set airport_id when creating a new record
            if (!$model->airport_id && auth()->check()) {
                $model->airport_id = auth()->user()->airport_id;
            }
        });

        // Apply airport scope if not Super Admin
        if (!auth()->user()?->hasRole('Super Admin')) {
            static::addGlobalScope('airport', function ($query) {
                $query->where('airport_id', auth()->user()->airport_id);
            });
        }
    }

    public function scopeForAirport($query, $airportId = null)
    {
        return $query->where('airport_id', $airportId ?? auth()->user()->airport_id);
    }
}