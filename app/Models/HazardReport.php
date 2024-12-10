<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CalculatesRiskRating;

class HazardReport extends Model
{
    use HasFactory;
    use CalculatesRiskRating;

    protected $fillable = [
        'reporter_name',
        'reporter_mobile',
        'reporter_email',
        'organization',
        'hazard_date',
        'hazard_time',
        'hazard_location',
        'hazard_description',
        'hazard_reason',
        'severity',
        'likelihood',
        'risk_rating',
        'corrective_actions',
        'attachments'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->severity && $model->likelihood) {
                $model->risk_rating = static::calculateRiskRating($model->severity, $model->likelihood);
            }
        });
    }

    public function getRiskLevelAttribute(): string
    {
        return static::getRiskLevel($this->risk_rating);
    }

    protected static array $riskColors = [
        // Red zone combinations
        '5A' => '#FF0000', // High risk
        '5B' => '#FF0000',
        '5C' => '#FF0000',
        '4A' => '#FF0000',
        '4B' => '#FF0000',
        '3A' => '#FF0000',
        
        // Yellow zone combinations
        '5D' => '#FFD700',
        '5E' => '#FFD700',
        '4C' => '#FFD700',
        '4D' => '#FFD700',
        '4E' => '#FFD700',
        '3B' => '#FFD700',
        '3C' => '#FFD700',
        '3D' => '#FFD700',
        '2A' => '#FFD700',
        '2B' => '#FFD700',
        '2C' => '#FFD700',
        
        // Green zone combinations
        '3E' => '#008000',
        '2D' => '#008000',
        '2E' => '#008000',
        '1A' => '#008000',
        '1B' => '#008000',
        '1C' => '#008000',
        '1D' => '#008000',
        '1E' => '#008000',
    ];

    public static function getRiskColor(string $riskRating): string
    {
        return self::$riskColors[$riskRating] ?? '#808080';
    }

    protected $casts = [
        'hazard_date' => 'date',
        'hazard_time' => 'datetime',
        'attachments' => 'array'
    ];
}