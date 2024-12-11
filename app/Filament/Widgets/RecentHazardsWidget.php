<?php

namespace App\Filament\Widgets;

use App\Models\BirdEntry;
use App\Models\HazardReport;
use App\Models\RiskAssessment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecentHazardsWidget extends BaseWidget
{
    protected ?string $heading = 'Recent Hazard Reports';
    protected int|string|array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        return HazardReport::latest()
            ->take(5)
            ->get()
            ->map(function ($hazard) {
                return Stat::make($hazard->hazard_location, $hazard->created_at->diffForHumans())
                    ->description($hazard->hazard_description)
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color(match ($hazard->risk_level) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    });
            })
            ->toArray();
    }
}