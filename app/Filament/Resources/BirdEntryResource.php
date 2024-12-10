<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BirdEntryResource\Pages;
use App\Models\BirdEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BirdEntryResource extends Resource
{
    protected static ?string $model = BirdEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Wildlife Management';
    
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Bird Entry';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bird Information')
                    ->schema([
                        Forms\Components\TextInput::make('common_english_name')
                            ->label('Common English Name')
                            ->required(),
                        Forms\Components\TextInput::make('local_name')
                            ->label('Local Name (Dhivehi)')
                            ->required(),
                        Forms\Components\TextInput::make('scientific_name')
                            ->label('Scientific Name')
                            ->required(),
                        Forms\Components\TextInput::make('species')
                            ->required(),
                        Forms\Components\TextInput::make('mass_weight')
                            ->label('Mass/Weight')
                            ->required()
                            ->suffix('g'),
                        Forms\Components\TextInput::make('flight_speed')
                            ->label('Flight Speed')
                            ->required()
                            ->suffix('km/h'),
                        Forms\Components\TextInput::make('length')
                            ->required()
                            ->suffix('cm'),
                        Forms\Components\TextInput::make('wingspan')
                            ->required()
                            ->suffix('cm'),
                    ])->columns(2),

                Forms\Components\Section::make('Characteristics')
                    ->schema([
                        Forms\Components\Toggle::make('is_migratory')
                            ->label('Migratory')
                            ->inline(false),
                        Forms\Components\Textarea::make('habitat')
                            ->required(),
                        Forms\Components\TextInput::make('food_diet')
                            ->label('Food/Diet')
                            ->required(),
                        Forms\Components\Textarea::make('appearance')
                            ->required(),
                        Forms\Components\Toggle::make('has_flocks')
                            ->label('Flocks')
                            ->inline(false),
                    ])->columns(2),

                Forms\Components\Section::make('Observation Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date_found')
                            ->required(),
                        Forms\Components\Textarea::make('remarks')
                            ->nullable(),
                        Forms\Components\TextInput::make('entered_by')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Attachments')
                ->schema([
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Attach Picture')
                        ->image()
                        ->directory('bird-entry'),
                    ]),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('species'),
                Tables\Filters\TernaryFilter::make('has_flocks')
                    ->label('Has Flocks'),
                Tables\Filters\TernaryFilter::make('is_migratory')
                    ->label('Migratory'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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