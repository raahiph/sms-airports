<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OccurrenceResource\Pages;
use App\Models\Occurrence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OccurrenceResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('airport_id', auth()->user()->airport_id);
        }
        
        return $query;
    }

    protected static ?string $model = Occurrence::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Safety Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Occurrences';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reporter Information')
                    ->schema([
                        Forms\Components\TextInput::make('reporter_name')
                        ->label('Full Name')
                        ->default(fn () => auth()->user()->name)
                        ->required()
                        ->disabled(),
                    Forms\Components\TextInput::make('reporter_mobile')
                        ->label('Mobile')
                        ->tel()
                        ->default(fn () => auth()->user()->contact_number)
                        ->required()
                        ->disabled(),
                    Forms\Components\TextInput::make('reporter_email')
                        ->label('Email')
                        ->email()
                        ->default(fn () => auth()->user()->email)
                        ->required()
                        ->disabled(),
                    Forms\Components\TextInput::make('airport')
                        ->label('Airport')
                        ->default(fn () => auth()->user()->airport)
                        ->required()
                        ->disabled(),
                ])->columns(2),

                Forms\Components\Section::make('Occurrence Details')
                    ->schema([
                        Forms\Components\DatePicker::make('occurrence_date')
                            ->required(),
                        Forms\Components\TimePicker::make('occurrence_time')
                            ->required(),
                        Forms\Components\TextInput::make('occurrence_location')
                            ->required(),
                        Forms\Components\Select::make('occurrence_type')
                            ->options(Occurrence::getOccurrenceTypes())
                            ->required(),
                        Forms\Components\RichEditor::make('occurrence_description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('immediate_actions_taken')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Impact Assessment')
                    ->schema([
                        Forms\Components\Toggle::make('injuries_reported')
                            ->reactive(),
                        Forms\Components\Textarea::make('injury_details')
                            ->visible(fn (Forms\Get $get) => $get('injuries_reported'))
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('damage_reported')
                            ->reactive(),
                        Forms\Components\Textarea::make('damage_details')
                            ->visible(fn (Forms\Get $get) => $get('damage_reported'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('estimated_cost')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Forms\Get $get) => $get('damage_reported')),
                    ]),

                Forms\Components\Section::make('Weather Conditions')
                    ->schema([
                        Forms\Components\TextInput::make('weather_conditions'),
                        Forms\Components\TextInput::make('visibility'),
                        Forms\Components\TextInput::make('wind_direction'),
                        Forms\Components\TextInput::make('wind_speed'),
                    ])->columns(2),

                Forms\Components\Section::make('Aircraft Details')
                    ->schema([
                        Forms\Components\TextInput::make('aircraft_type'),
                        Forms\Components\TextInput::make('aircraft_registration'),
                        Forms\Components\Select::make('flight_phase')
                            ->options([
                                'taxi' => 'Taxi',
                                'takeoff' => 'Take-off',
                                'climb' => 'Climb',
                                'cruise' => 'Cruise',
                                'descent' => 'Descent',
                                'approach' => 'Approach',
                                'landing' => 'Landing',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('occurrences')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurrence_date')
                    ->sortable()
                    ->date(),
                Tables\Columns\TextColumn::make('occurrence_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('occurrence_type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('injuries_reported')
                    ->boolean(),
                Tables\Columns\IconColumn::make('damage_reported')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'closed' => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('occurrence_type')
                    ->options(Occurrence::getOccurrenceTypes()),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOccurrences::route('/'),
            'create' => Pages\CreateOccurrence::route('/create'),
            'edit' => Pages\EditOccurrence::route('/{record}/edit'),
        ];
    }
}