<?php

namespace App\Traits;

trait CalculatesRiskRating
{
    protected static array $severityLevels = [
        'A' => 'Catastrophic', // Results in an accident, death or equipment destroyed
        'B' => 'Hazardous',    // Serious injury or major equipment damage
        'C' => 'Major',        // Serious incident or injury
        'D' => 'Minor',        // Results in a minor incident
        'E' => 'Negligible'    // Nuisance of little consequence
    ];

    protected static array $likelihoodLevels = [
        '5' => 'Frequent',      // Likely to occur many times
        '4' => 'Occasional',    // Likely to occur sometimes
        '3' => 'Remote',        // Unlikely to occur but possible
        '2' => 'Improbable',    // Very unlikely to occur
        '1' => 'Extremely improbable' // Almost inconceivable
    ];

    public static function calculateRiskRating(string $severity, string $likelihood): string
    {
        return $likelihood . $severity;
    }

    public static function getRiskLevel(string $riskRating): string
    {
        // Get first character (likelihood) and second character (severity)
        $likelihood = $riskRating[0] ?? '';
        $severity = $riskRating[1] ?? '';

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

    public static function getSeverityOptions(): array
    {
        return static::$severityLevels;
    }

    public static function getLikelihoodOptions(): array
    {
        return static::$likelihoodLevels;
    }
}