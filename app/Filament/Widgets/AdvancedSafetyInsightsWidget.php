<?php

namespace App\Filament\Widgets;

use App\Services\AdvancedAnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdvancedSafetyInsightsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $analyticsService = app(AdvancedAnalyticsService::class);
        $analysis = $analyticsService->getComprehensiveAnalysis();

        return [
            Stat::make('Total Occurrences', $analysis['occurrence_analysis']['basic_stats']['total'] ?? '0')
                ->description('Total safety events recorded')
                ->descriptionIcon('heroicon-m-document-text'),

            Stat::make('Risk Level', $analysis['bird_strike_predictions']['risk_level'] ?? 'Unknown')
                ->description('Current bird strike risk')
                ->color(match ($analysis['bird_strike_predictions']['risk_level'] ?? 'unknown') {
                    'high' => 'danger',
                    'medium' => 'warning',
                    'low' => 'success',
                    default => 'gray',
                }),

            Stat::make('Active Hotspots', count($analysis['risk_hotspots'] ?? []))
                ->description('High-risk locations identified')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('danger'),
        ];
    }
}