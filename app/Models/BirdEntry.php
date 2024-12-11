<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAirport;

class BirdEntry extends Model
{
    use HasFactory;
    use BelongsToAirport;

    protected $fillable = [
        'common_english_name',
        'local_name',
        'scientific_name',
        'species',
        'mass_weight',
        'flight_speed',
        'length',
        'wingspan',
        'is_migratory',
        'habitat',
        'food_diet',
        'appearance',
        'has_flocks',
        'date_found',
        'remarks',
        'entered_by'
    ];

    protected $casts = [
        'date_found' => 'date',
        'has_flocks' => 'boolean',
        'is_migratory' => 'boolean'
    ];
}