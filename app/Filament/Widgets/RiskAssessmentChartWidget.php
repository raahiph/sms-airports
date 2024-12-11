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

class RiskAssessmentChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Risk Assessments Status';
    
    protected function getData(): array
    {
        $statusCounts = RiskAssessment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        return [
            'datasets' => [
                [
                    'label' => 'Risk Assessments by Status',
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        'rgb(255, 205, 86)', // Generated
                        'rgb(54, 162, 235)', // Reviewed
                        'rgb(75, 192, 192)', // Approved
                        'rgb(255, 99, 132)', // Rejected
                    ],
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($statusCounts)),
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
}
