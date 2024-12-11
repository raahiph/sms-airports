<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait HasUserManagementAccess
{
    public static function shouldRegisterNavigation(): bool
    {
        // For RoleResource, only Super Admin can see it
        if (static::class === 'App\Filament\Resources\RoleResource') {
            return auth()->user()->hasRole('Super Admin');
        }
        
        // For other resources in User Management
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canViewAny(): bool
    {
        if (static::class === 'App\Filament\Resources\RoleResource') {
            return auth()->user()->hasRole('Super Admin');
        }
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canCreate(): bool
    {
        if (static::class === 'App\Filament\Resources\RoleResource') {
            return auth()->user()->hasRole('Super Admin');
        }
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canEdit(Model $record): bool
    {
        if (static::class === 'App\Filament\Resources\RoleResource') {
            return auth()->user()->hasRole('Super Admin');
        }
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canDelete(Model $record): bool
    {
        if (static::class === 'App\Filament\Resources\RoleResource') {
            return auth()->user()->hasRole('Super Admin');
        }
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Admin')) {
            return static::adminQueryRestrictions($query);
        }

        return $query;
    }

    protected static function adminQueryRestrictions(Builder $query): Builder
    {
        return $query;
    }
}