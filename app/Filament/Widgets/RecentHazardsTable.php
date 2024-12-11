<?php

namespace App\Filament\Widgets;

use App\Models\HazardReport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentHazardsTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Hazard Reports';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HazardReport::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('hazard_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hazard_location')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hazard_description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('reporter_name')
                    ->label('Reporter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('risk_rating')
                    ->label('Risk Rating')
                    ->badge()
                    ->color(fn (string $state): string => match (HazardReport::getRiskColor($state)) {
                        '#FF0000' => 'danger',
                        '#FFD700' => 'warning',
                        '#008000' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => 
                        $state . ' - ' . match (HazardReport::getRiskColor($state)) {
                            '#FF0000' => 'High Risk',
                            '#FFD700' => 'Medium Risk',
                            '#008000' => 'Low Risk',
                            default => 'Undefined',
                        }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (HazardReport $record): string => route('filament.admin.resources.hazard-reports.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}