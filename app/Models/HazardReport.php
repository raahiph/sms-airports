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
        if (!$this->risk_rating) {
            return 'undefined';
        }
        return static::getRiskLevel($this->risk_rating);
    }

    public static function getRiskLevel(?string $riskRating): string
    {
        if (!$riskRating) {
            return 'undefined';
        }

        // High Risk (Red) combinations
        $highRisk = ['5A', '5B', '5C', '4A', '4B', '3A'];
        
        // Medium Risk (Yellow) combinations
        $mediumRisk = ['5D', '5E', '4C', '4D', '4E', '3B', '3C', '3D', '2A', '2B', '2C'];
        
        // Low Risk (Green) combinations
        $lowRisk = ['3E', '2D', '2E', '1A', '1B', '1C', '1D', '1E'];

        if (in_array($riskRating, $highRisk)) {
            return 'high';
        } elseif (in_array($riskRating, $mediumRisk)) {
            return 'medium';
        } elseif (in_array($riskRating, $lowRisk)) {
            return 'low';
        }

        return 'undefined';
    }

        public static function getRiskColor(?string $riskRating): string
    {
        if (!$riskRating) {
            return '#808080'; // Gray for undefined
        }

        return match (self::getRiskLevel($riskRating)) {
            'high' => '#FF0000',    // Red
            'medium' => '#FFD700',  // Yellow
            'low' => '#008000',     // Green
            default => '#808080'    // Gray
        };
    }

    protected $casts = [
        'hazard_date' => 'date',
        'hazard_time' => 'datetime',
        'attachments' => 'array'
    ];
}