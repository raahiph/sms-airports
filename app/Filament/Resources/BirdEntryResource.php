<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BirdEntryResource\Pages;
use App\Models\BirdEntry;
use App\Services\BirdVisionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;


class BirdEntryResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('airport_id', auth()->user()->airport_id);
        }
        
        return $query;
    }

    protected static ?string $model = BirdEntry::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Wildlife Management';
    protected static ?string $navigationLabel = 'Bird Data Entry';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Image Analysis')
                    ->description('Upload a bird image for automatic information detection')
                    ->schema([
                        Forms\Components\FileUpload::make('bird_image')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) return;
                                
                                try {
                                    $visionService = app(BirdVisionService::class);
                                    $birdData = $visionService->analyzeImage($state);

                                    // Set form values based on AI analysis
                                    $set('common_english_name', $birdData['common_english_name'] ?? null);
                                    $set('local_name', $birdData['local_name'] ?? null);
                                    $set('scientific_name', $birdData['scientific_name'] ?? null);
                                    $set('species', $birdData['species_family'] ?? null);
                                    $set('mass_weight', $birdData['mass_weight'] ?? null);
                                    $set('flight_speed', $birdData['average flight speed'] ?? null);
                                    $set('length', $birdData['length'] ?? null);
                                    $set('wingspan', $birdData['wingspan'] ?? null);
                                    $set('is_migratory', str_contains(strtolower($birdData['migratory status (yes/no)'] ?? ''), 'yes'));
                                    $set('habitat', $birdData['typical_habitat'] ?? null);
                                    $set('food_diet', $birdData['diet'] ?? null);
                                    $set('appearance', $birdData['physical_appearance'] ?? null);
                                    $set('has_flocks', str_contains(strtolower($birdData['flocking_behavior'] ?? ''), 'yes'));

                                    Notification::make()
                                        ->title('Image Analysis Complete')
                                        ->success()
                                        ->send();

                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Image Analysis Failed')
                                        ->body('Could not analyze the image. Please fill in the details manually.')
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Bird Information')
                    ->schema([
                        Forms\Components\TextInput::make('common_english_name')
                            ->label('Common English Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('local_name')
                            ->label('Local Name (Dhivehi)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('scientific_name')
                            ->label('Scientific Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('species')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('mass_weight')
                            ->label('Mass / Weight')
                            ->required()
                            ->suffix('g')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('flight_speed')
                            ->label('Flight Speed')
                            ->required()
                            ->suffix('km/h')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('length')
                            ->required()
                            ->suffix('cm')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('wingspan')
                            ->required()
                            ->suffix('cm')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Characteristics')
                    ->schema([
                        Forms\Components\Toggle::make('is_migratory')
                            ->label('Migratory'),
                            
                        Forms\Components\Textarea::make('habitat')
                            ->label('Habitat')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('food_diet')
                            ->label('Food/Diet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('appearance')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('has_flocks')
                            ->label('Flocks'),

                        Forms\Components\DatePicker::make('date_found')
                            ->required()
                            ->maxDate(now()),

                        Forms\Components\Textarea::make('remarks')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                            Forms\Components\Hidden::make('entered_by')
                            ->default(fn () => auth()->user()->name),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('common_english_name')
                    ->label('English Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('local_name')
                    ->label('Local Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('species')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_flocks')
                    ->label('Flocks')
                    ->boolean(),
                Tables\Columns\TextColumn::make('date_found')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entered_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('species')
                    ->options(fn () => BirdEntry::distinct()->pluck('species', 'species')->toArray()),
                
                Filter::make('is_migratory')
                    ->label('Migratory Birds')
                    ->query(fn (Builder $query): Builder => $query->where('is_migratory', true)),
                
                Filter::make('has_flocks')
                    ->label('Birds with Flocks')
                    ->query(fn (Builder $query): Builder => $query->where('has_flocks', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBirdEntries::route('/'),
            'create' => Pages\CreateBirdEntry::route('/create'),
            'edit' => Pages\EditBirdEntry::route('/{record}/edit'),
        ];
    }
}