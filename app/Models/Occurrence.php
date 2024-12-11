<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occurrence extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reporter_name',
        'reporter_mobile',
        'reporter_email',
        'organization',
        'occurrence_date',
        'occurrence_time',
        'occurrence_location',
        'occurrence_type',
        'occurrence_description',
        'immediate_actions_taken',
        'investigation_findings',
        'root_causes',
        'recommendations',
        'investigation_status',
        'injuries_reported',
        'injury_details',
        'damage_reported',
        'damage_details',
        'estimated_cost',
        'weather_conditions',
        'visibility',
        'wind_direction',
        'wind_speed',
        'aircraft_type',
        'aircraft_registration',
        'flight_phase',
        'attachments',
        'status',
        'severity_level',
        'probability_level',
        'risk_level'
    ];

    protected $casts = [
        'occurrence_date' => 'date',
        'occurrence_time' => 'datetime',
        'injuries_reported' => 'boolean',
        'damage_reported' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'attachments' => 'array'
    ];

    public static function getOccurrenceTypes(): array
    {
        return [
            'bird_strike' => 'Bird Strike',
            'runway_incursion' => 'Runway Incursion',
            'wildlife_strike' => 'Wildlife Strike',
            'ground_handling' => 'Ground Handling Incident',
            'maintenance_error' => 'Maintenance Error',
            'security_breach' => 'Security Breach',
            'communication_error' => 'Communication Error',
            'equipment_failure' => 'Equipment Failure',
            'other' => 'Other'
        ];
    }
}