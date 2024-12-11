<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazardReportResource\Pages;
use App\Models\HazardReport;
use App\Models\RiskAssessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Log;
use App\Services\ClaudeAssessmentService;
use App\Traits\GeneratesAiAssessment;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter\DateFilter;


class HazardReportResource extends Resource
{
    protected static ?string $model = HazardReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Safety Management';
    
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Hazard Reports';

    use GeneratesAiAssessment;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reporter Information')
                    ->schema([
                        Forms\Components\TextInput::make('reporter_name')
                            ->label('Full Name')
                            ->required(),
                        Forms\Components\TextInput::make('reporter_mobile')
                            ->label('Mobile/Tel')
                            ->tel()
                            ->required(),
                        Forms\Components\TextInput::make('reporter_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('organization')
                            ->label('Organization/Airport')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Hazard Identification')
                    ->schema([
                        Forms\Components\DatePicker::make('hazard_date')
                            ->label('Date Hazard Identified')
                            ->required(),
                        Forms\Components\TimePicker::make('hazard_time')
                            ->label('Time Hazard Identified')
                            ->required(),
                        Forms\Components\TextInput::make('hazard_location')
                            ->label('Location of Hazard')
                            ->required(),
                        Forms\Components\RichEditor::make('hazard_description')
                            ->label('Describe the Hazard')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ]),
                        Forms\Components\RichEditor::make('hazard_reason')
                            ->label('Why/How is it a Hazard')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])->columns(2),

                    Forms\Components\Section::make('Risk Assessment Matrix')
                    ->schema([
                        Forms\Components\Select::make('severity')
                            ->label('Severity of Consequences')
                            ->options([
                                'A' => 'A - Catastrophic (Death/Equipment Destroyed)',
                                'B' => 'B - Hazardous (Serious Injury/Major Damage)',
                                'C' => 'C - Major (Serious Incident/Injury)',
                                'D' => 'D - Minor (Minor Incident)',
                                'E' => 'E - Negligible (Little Consequence)'
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                if ($state && $get('likelihood')) {
                                    $riskRating = HazardReport::calculateRiskRating($state, $get('likelihood'));
                                    $color = HazardReport::getRiskColor($riskRating);
                                    $set('calculated_risk_rating', [
                                        'rating' => $riskRating,
                                        'color' => $color
                                    ]);
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\Select::make('likelihood')
                            ->label('Likelihood of Occurrence')
                            ->options([
                                '5' => '5 - Frequent (Likely to occur many times)',
                                '4' => '4 - Occasional (Likely to occur sometimes)',
                                '3' => '3 - Remote (Unlikely but possible)',
                                '2' => '2 - Improbable (Very unlikely)',
                                '1' => '1 - Extremely Improbable (Almost inconceivable)'
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                if ($state && $get('severity')) {
                                    $riskRating = HazardReport::calculateRiskRating($get('severity'), $state);
                                    $color = HazardReport::getRiskColor($riskRating);
                                    $set('calculated_risk_rating', [
                                        'rating' => $riskRating,
                                        'color' => $color
                                    ]);
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('calculated_risk_rating')
                            ->label('Risk Rating')
                            ->content(function ($state) {
                                if (!$state) return 'Select severity and likelihood to calculate risk rating';
                                
                                $rating = $state['rating'] ?? '';
                                $color = $state['color'] ?? '#808080';
                                
                                $colorMap = [
                                    '#FF0000' => 'High Risk - Immediate Action Required',
                                    '#FFD700' => 'Medium Risk - Caution Required',
                                    '#008000' => 'Low Risk - Monitor',
                                ];

                                $description = $colorMap[$color] ?? 'Undefined Risk Level';
                                
                                return new \Illuminate\Support\HtmlString("
                                    <div style='padding: 10px; background-color: {$color}; color: white; border-radius: 5px; text-align: center;'>
                                        <strong>Rating: {$rating}</strong><br>
                                        {$description}
                                    </div>
                                ");
                            })
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('risk_rating'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Corrective Actions')
                    ->schema([
                        Forms\Components\RichEditor::make('corrective_actions')
                            ->label('Suggested Corrective Actions')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ]),
                    ]),

                Forms\Components\Section::make('Attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attached Pictures')
                            ->multiple()
                            ->image()
                            ->directory('hazard-reports'),
                    ]),

                    Forms\Components\Section::make('Risk Assessment')
                    ->description('Generate or view AI risk assessments for this hazard.')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generateAssessment')
                                ->label('Generate New Risk Assessment')
                                ->icon('heroicon-m-sparkles')
                                ->button()
                                ->color('primary')
                                ->requiresConfirmation()
                                ->modalHeading('Generate AI Risk Assessment')
                                ->modalDescription('Generate a detailed risk assessment using AI?')
                                ->visible(fn (?HazardReport $record) => $record !== null && $record->exists)
                                ->action(fn (HazardReport $record) => static::generateAiAssessment($record)),

                                Forms\Components\Actions\Action::make('viewAssessments')
                                ->label('View Existing Assessments')
                                ->icon('heroicon-o-document-text')
                                ->button()
                                ->color('gray')
                                ->visible(fn (?HazardReport $record) => $record !== null && $record->exists)
                                ->url(fn (HazardReport $record) => 
                                    RiskAssessmentResource::getUrl('index', [
                                        'tableFilters' => [
                                            'hazard_report_id' => ['value' => $record->id]
                                        ]
                                    ])),
                        ])->columnSpanFull(),
                    ])
                    ->visible(fn (?HazardReport $record) => $record !== null)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reporter_name')
                    ->label('Reporter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hazard_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hazard_location')
                    ->label('Location')
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
                        })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('generateAssessment')
                    ->label('Generate Risk Assessment')
                    ->icon('heroicon-m-sparkles')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Generate AI Risk Assessment')
                    ->modalDescription('This will analyze the hazard and generate a detailed risk assessment using AI. Continue?')
                    ->modalSubmitActionLabel('Generate')
                    ->action(fn (HazardReport $record) => static::generateAiAssessment($record)),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\BulkAction::make('generateAssessments')
                    // ->label('Generate Risk Assessments')
                    // ->icon('heroicon-o-sparkles')
                    // ->color('primary')
                    // ->requiresConfirmation()
                    // ->action(function ($records) {
                    //     foreach ($records as $record) {
                    //         // Generate assessment for each selected record
                    //     }
                    // }),
                ]),
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
            'index' => Pages\ListHazardReports::route('/'),
            'create' => Pages\CreateHazardReport::route('/create'),
            'edit' => Pages\EditHazardReport::route('/{record}/edit'),
        ];
    }
}