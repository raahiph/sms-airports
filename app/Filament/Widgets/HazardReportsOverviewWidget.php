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

class HazardReportsOverviewWidget extends BaseWidget
{
    protected ?string $heading = 'Hazard Reports Overview';
    
    protected function getStats(): array
    {
        $totalHazards = HazardReport::count();
        $highRiskHazards = HazardReport::whereIn('risk_rating', ['5A', '5B', '5C', '4A', '4B', '3A'])->count();
        $pendingAssessments = HazardReport::whereDoesntHave('riskAssessments')->count();
        
        return [
            Stat::make('Total Hazards', $totalHazards)
                ->description('Total reported hazards')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('High Risk', $highRiskHazards)
                ->description('High risk hazards')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('danger'),

            Stat::make('Pending Assessment', $pendingAssessments)
                ->description('Awaiting risk assessment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
