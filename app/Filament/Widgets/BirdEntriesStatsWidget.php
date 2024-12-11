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

class BirdEntriesStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Bird Entries Overview';
    
    protected function getStats(): array
    {
        $totalBirds = BirdEntry::count();
        $migratoryBirds = BirdEntry::where('is_migratory', true)->count();
        $flockingBirds = BirdEntry::where('has_flocks', true)->count();
        
        return [
            Stat::make('Total Bird Species', $totalBirds)
                ->description('Total number of recorded species')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([7, 4, 6, 8, $totalBirds]),

            Stat::make('Migratory Birds', $migratoryBirds)
                ->description('Number of migratory species')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Flocking Birds', $flockingBirds)
                ->description('Birds that form flocks')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
        ];
    }
}