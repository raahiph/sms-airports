<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasUserManagementAccess;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    use HasUserManagementAccess;

    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles & Permissions';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => 
                                !auth()->user()->hasRole('Super Admin') || 
                                ($record && in_array($record->name, ['Super Admin', 'Admin']))
                            ),
                    ]),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\Tabs::make('Permissions')
                            ->tabs([
                                // User Management Tab
                                Forms\Components\Tabs\Tab::make('User Management')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('permissions')
                                            ->relationship('permissions', 'name')
                                            ->options(function () {
                                                return Permission::where('name', 'like', '%user%')
                                                    ->orWhere('name', 'like', '%role%')
                                                    ->pluck('name', 'id');
                                            })
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->searchable()
                                    ]),

                                // Safety Management Tab
                                Forms\Components\Tabs\Tab::make('Safety Management')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('permissions')
                                            ->relationship('permissions', 'name')
                                            ->options(function () {
                                                return Permission::where('name', 'like', '%hazard%')
                                                    ->orWhere('name', 'like', '%risk%')
                                                    ->orWhere('name', 'like', '%occurrence%')
                                                    ->pluck('name', 'id');
                                            })
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->searchable()
                                    ]),

                                // Wildlife Management Tab
                                Forms\Components\Tabs\Tab::make('Wildlife Management')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('permissions')
                                            ->relationship('permissions', 'name')
                                            ->options(function () {
                                                return Permission::where('name', 'like', '%bird%')
                                                    ->orWhere('name', 'like', '%wildlife%')
                                                    ->pluck('name', 'id');
                                            })
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->searchable()
                                    ]),

                                // Other Permissions Tab
                                Forms\Components\Tabs\Tab::make('Other Permissions')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('permissions')
                                            ->relationship('permissions', 'name')
                                            ->options(function () {
                                                return Permission::where('name', 'not like', '%user%')
                                                    ->where('name', 'not like', '%role%')
                                                    ->where('name', 'not like', '%hazard%')
                                                    ->where('name', 'not like', '%risk%')
                                                    ->where('name', 'not like', '%occurrence%')
                                                    ->where('name', 'not like', '%bird%')
                                                    ->where('name', 'not like', '%wildlife%')
                                                    ->pluck('name', 'id');
                                            })
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->searchable()
                                    ]),
                            ])
                            ->columnSpanFull()
                    ])
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->badge()
                    ->label('Permissions'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->badge()
                    ->label('Users'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => 
                        auth()->user()->hasRole('Super Admin') && 
                        !in_array($record->name, ['Super Admin', 'Admin', 'User'])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Super Admin'))
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if (!in_array($record->name, ['Super Admin', 'Admin', 'User'])) {
                                    $record->delete();
                                }
                            });
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}