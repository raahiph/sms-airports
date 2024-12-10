<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    protected $fillable = [
        'hazard_report_id',
        'executive_summary',
        'risk_analysis',
        'impact_assessment',
        'mitigation_measures',
        'implementation_timeline',
        'monitoring_requirements',
        'generated_by',
        'status',
        'generated_at'
    ];

    protected $casts = [
        'generated_at' => 'datetime'
    ];

    public function hazardReport(): BelongsTo
    {
        return $this->belongsTo(HazardReport::class);
    }
}