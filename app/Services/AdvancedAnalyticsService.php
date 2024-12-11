<?php

namespace App\Services;

use App\Models\Occurrence;
use App\Models\HazardReport;
use App\Models\BirdEntry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AdvancedAnalyticsService
{
    protected $pythonApiEndpoint;
    protected $cacheTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->pythonApiEndpoint = config('services.ml.endpoint');
    }

    /**
     * Get comprehensive safety analysis including all data sources
     */
    public function getComprehensiveAnalysis(): array
    {
        return Cache::remember('comprehensive_analysis', $this->cacheTimeout, function () {
            return [
                'occurrence_analysis' => $this->analyzeOccurrences(),
                'bird_strike_predictions' => $this->getBirdStrikePredictions(),
                'risk_hotspots' => $this->analyzeRiskHotspots(),
                'temporal_patterns' => $this->analyzeTemporalPatterns(),
                'correlations' => $this->analyzeCorrelations(),
                'recommendations' => $this->generateRecommendations(),
            ];
        });
    }

    /**
     * Analyze occurrences for patterns and insights
     */
    protected function analyzeOccurrences(): array
    {
        $occurrences = Occurrence::get();
        
        try {
            $response = Http::post("{$this->pythonApiEndpoint}/analyze-occurrences", [
                'occurrences' => $occurrences->map(function($occurrence) {
                    return [
                        'type' => $occurrence->occurrence_type,
                        'date' => $occurrence->occurrence_date,
                        'time' => $occurrence->occurrence_time,
                        'location' => $occurrence->occurrence_location,
                        'weather' => [
                            'conditions' => $occurrence->weather_conditions,
                            'visibility' => $occurrence->visibility,
                            'wind_direction' => $occurrence->wind_direction,
                            'wind_speed' => $occurrence->wind_speed,
                        ],
                        'has_injuries' => $occurrence->injuries_reported,
                        'has_damage' => $occurrence->damage_reported,
                        'flight_phase' => $occurrence->flight_phase,
                    ];
                })->toArray()
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Occurrence analysis failed', ['error' => $e->getMessage()]);
            return [
                'error' => 'Analysis failed',
                'basic_stats' => $this->getBasicOccurrenceStats($occurrences)
            ];
        }
    }

    /**
     * Get bird strike predictions based on current conditions
     */
    protected function getBirdStrikePredictions(): array
    {
        $birdData = $this->getProcessedBirdData();
        $weatherData = $this->getCurrentWeatherData();
        
        try {
            $response = Http::post("{$this->pythonApiEndpoint}/predict-bird-strikes", [
                'historical_data' => $birdData,
                'current_weather' => $weatherData,
                'time_of_year' => Carbon::now()->format('m'),
                'time_of_day' => Carbon::now()->format('H'),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Bird strike prediction failed', ['error' => $e->getMessage()]);
            return [
                'risk_level' => 'unknown',
                'confidence' => 0,
            ];
        }
    }

    /**
     * Analyze geographical risk hotspots
     */
    protected function analyzeRiskHotspots(): array
    {
        $occurrences = Occurrence::all();
        $hazards = HazardReport::all();
        
        $locations = collect()
            ->merge($occurrences->pluck('occurrence_location'))
            ->merge($hazards->pluck('hazard_location'))
            ->unique();
            
        $hotspots = $locations->map(function($location) use ($occurrences, $hazards) {
            return [
                'location' => $location,
                'occurrences' => $occurrences->where('occurrence_location', $location)->count(),
                'hazards' => $hazards->where('hazard_location', $location)->count(),
                'risk_level' => $this->calculateLocationRiskLevel($location, $occurrences, $hazards)
            ];
        });
        
        try {
            $response = Http::post("{$this->pythonApiEndpoint}/analyze-hotspots", [
                'hotspots' => $hotspots->toArray()
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Hotspot analysis failed', ['error' => $e->getMessage()]);
            return $hotspots->sortByDesc('risk_level')->values()->toArray();
        }
    }

    /**
     * Analyze temporal patterns across all safety data
     */
    protected function analyzeTemporalPatterns(): array
    {
        $data = [
            'occurrences' => $this->getTemporalData(Occurrence::class, 'occurrence_date'),
            'hazards' => $this->getTemporalData(HazardReport::class, 'hazard_date'),
            'bird_sightings' => $this->getTemporalData(BirdEntry::class, 'date_found'),
        ];

        try {
            $response = Http::post("{$this->pythonApiEndpoint}/analyze-temporal", [
                'data' => $data
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Temporal analysis failed', ['error' => $e->getMessage()]);
            return $data;
        }
    }

    /**
     * Analyze correlations between different types of safety events
     */
    protected function analyzeCorrelations(): array
    {
        $data = [
            'bird_strikes' => $this->getBirdStrikeData(),
            'weather_conditions' => $this->getWeatherData(),
            'operational_factors' => $this->getOperationalData(),
        ];

        try {
            $response = Http::post("{$this->pythonApiEndpoint}/analyze-correlations", [
                'data' => $data
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Correlation analysis failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Generate safety recommendations based on all available data
     */
    protected function generateRecommendations(): array
    {
        $data = [
            'occurrences' => Occurrence::all(),
            'hazards' => HazardReport::all(),
            'birds' => BirdEntry::all(),
        ];

        try {
            $response = Http::post("{$this->pythonApiEndpoint}/generate-recommendations", [
                'data' => $data
            ]);

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Recommendation generation failed', ['error' => $e->getMessage()]);
            return [
                'general' => $this->getBasicRecommendations(),
                'error' => 'Advanced recommendations unavailable'
            ];
        }
    }

    // Helper methods...
    protected function getBasicOccurrenceStats(Collection $occurrences): array
    {
        return [
            'total' => $occurrences->count(),
            'by_type' => $occurrences->groupBy('occurrence_type')->map->count(),
            'with_injuries' => $occurrences->where('injuries_reported', true)->count(),
            'with_damage' => $occurrences->where('damage_reported', true)->count(),
        ];
    }

    protected function calculateLocationRiskLevel(string $location, Collection $occurrences, Collection $hazards): string
    {
        $occurrenceCount = $occurrences->where('occurrence_location', $location)->count();
        $hazardCount = $hazards->where('hazard_location', $location)->count();
        
        $totalScore = ($occurrenceCount * 2) + $hazardCount;
        
        return match(true) {
            $totalScore >= 10 => 'high',
            $totalScore >= 5 => 'medium',
            default => 'low',
        };
    }

    protected function getTemporalData(string $model, string $dateField): array
    {
        return $model::selectRaw("DATE_FORMAT($dateField, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    protected function getProcessedBirdData(): array
    {
        return BirdEntry::select(
            'date_found',
            'is_migratory',
            'has_flocks',
            'mass_weight',
            'flight_speed',
            'wingspan'
        )->get()
        ->map(function($entry) {
            return [
                'date' => $entry->date_found,
                'is_migratory' => $entry->is_migratory,
                'has_flocks' => $entry->has_flocks,
                'mass' => (float) str_replace(['g', 'kg'], '', $entry->mass_weight),
                'speed' => (float) str_replace('km/h', '', $entry->flight_speed),
                'wingspan' => (float) str_replace('cm', '', $entry->wingspan)
            ];
        })
        ->toArray();
    }

    /**
     * Get current weather data
     */
    protected function getCurrentWeatherData(): array
    {
        // In a real application, this would fetch from a weather API
        return [
            'temperature' => 25,
            'wind_speed' => 10,
            'visibility' => 'good',
            'precipitation' => 0,
            'cloud_cover' => 'scattered'
        ];
    }

    /**
     * Get bird strike specific data
     */
    protected function getBirdStrikeData(): array
    {
        return Occurrence::where('occurrence_type', 'bird_strike')
            ->get()
            ->map(function($occurrence) {
                return [
                    'date' => $occurrence->occurrence_date,
                    'time' => $occurrence->occurrence_time,
                    'location' => $occurrence->occurrence_location,
                    'flight_phase' => $occurrence->flight_phase,
                    'damage_reported' => $occurrence->damage_reported,
                    'weather_conditions' => $occurrence->weather_conditions
                ];
            })
            ->toArray();
    }

    /**
     * Get weather-related data
     */
    protected function getWeatherData(): array
    {
        return Occurrence::whereNotNull('weather_conditions')
            ->get()
            ->map(function($occurrence) {
                return [
                    'date' => $occurrence->occurrence_date,
                    'conditions' => $occurrence->weather_conditions,
                    'visibility' => $occurrence->visibility,
                    'wind_direction' => $occurrence->wind_direction,
                    'wind_speed' => $occurrence->wind_speed
                ];
            })
            ->toArray();
    }

    /**
     * Get operational factors data
     */
    protected function getOperationalData(): array
    {
        return Occurrence::select(
            'occurrence_date',
            'occurrence_time',
            'occurrence_type',
            'flight_phase',
            'aircraft_type'
        )
        ->get()
        ->map(function($occurrence) {
            return [
                'date' => $occurrence->occurrence_date,
                'time' => $occurrence->occurrence_time,
                'type' => $occurrence->occurrence_type,
                'flight_phase' => $occurrence->flight_phase,
                'aircraft_type' => $occurrence->aircraft_type
            ];
        })
        ->toArray();
    }

    /**
     * Get basic recommendations when ML service is unavailable
     */
    protected function getBasicRecommendations(): array
    {
        return [
            'general' => [
                'Regularly review and update safety procedures',
                'Ensure proper reporting of all incidents',
                'Maintain regular staff training programs'
            ],
            'bird_strike' => [
                'Monitor bird activity patterns',
                'Maintain grass at recommended height',
                'Review wildlife management procedures'
            ],
            'operational' => [
                'Regular equipment maintenance checks',
                'Update communication protocols',
                'Review emergency response procedures'
            ]
        ];
    }
}

