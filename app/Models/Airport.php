<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    protected $fillable = [
        'code',
        'name',
        'country',
        'is_active'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hazardReports(): HasMany
    {
        return $this->hasMany(HazardReport::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Occurrence::class);
    }

    public function birdEntries(): HasMany
    {
        return $this->hasMany(BirdEntry::class);
    }
}