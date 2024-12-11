<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserManagementAccess;

class UserResource extends Resource
{
    use HasUserManagementAccess;
    
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 3;

    protected static function adminQueryRestrictions(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Super Admin');
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\TextInput::make('designation')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('department')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('airport')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_number')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),


                Forms\Components\Section::make('Role')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required()
                            ->options(function () {
                                if (auth()->user()->hasRole('Super Admin')) {
                                    // Super Admin can assign any role
                                    return \Spatie\Permission\Models\Role::all()->pluck('name', 'id');
                                } else {
                                    // Admin can only assign User role
                                    return \Spatie\Permission\Models\Role::where('name', 'User')->pluck('name', 'id');
                                }
                            })
                            ->visible(fn () => auth()->user()->hasRole(['Super Admin', 'Admin'])),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('designation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('airport')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_number'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}