<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiskAssessmentResource\Pages;
use App\Models\RiskAssessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\ClaudeAssessmentService;
use App\Traits\GeneratesAiAssessment;

class RiskAssessmentResource extends Resource
{
    protected static ?string $model = RiskAssessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Safety Management';
    
    protected static ?string $navigationLabel = 'Risk Assessments';
    
    protected static ?int $navigationSort = 2;

    use GeneratesAiAssessment;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Details')
                    ->schema([
                        Forms\Components\Select::make('hazard_report_id')
                            ->relationship('hazardReport', 'id')
                            ->label('Related Hazard Report')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\TextInput::make('generated_by')
                            ->default('AI Assistant')
                            ->required(),
                            
                        Forms\Components\Select::make('status')
                            ->options([
                                'generated' => 'Generated',
                                'reviewed' => 'Reviewed',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected'
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Assessment Content')
                    ->schema([
                        Forms\Components\Textarea::make('executive_summary')
                            ->label('Executive Summary')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('risk_analysis')
                            ->label('Risk Analysis')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('impact_assessment')
                            ->label('Impact Assessment')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('mitigation_measures')
                            ->label('Mitigation Measures')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('implementation_timeline')
                            ->label('Implementation Timeline')
                            ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('monitoring_requirements')
                            ->label('Monitoring Requirements')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hazard_report_id')
                    ->label('Hazard Report ID')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('generated_at')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('generated_by')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'generated',
                        'primary' => 'reviewed',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->defaultSort('generated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'generated' => 'Generated',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiskAssessments::route('/'),
            'create' => Pages\CreateRiskAssessment::route('/create'),
            'edit' => Pages\EditRiskAssessment::route('/{record}/edit'),
            'view' => Pages\ViewRiskAssessment::route('/{record}'),
        ];
    }
}