<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;  // Add this import

trait HasUserManagementAccess
{
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyPermission([
            'view_any_users',
            'view_any_roles',
            'view_any_permissions'
        ]);
    }

    public static function canViewAny(): bool
    {
        $model = static::getModel();
        return auth()->user()->hasPermissionTo('view_any_' . Str::plural(Str::snake(class_basename($model))));
    }

    public static function canCreate(): bool
    {
        $model = static::getModel();
        return auth()->user()->hasPermissionTo('create_' . Str::plural(Str::snake(class_basename($model))));
    }

    public static function canEdit(Model $record): bool
    {
        $model = static::getModel();
        return auth()->user()->hasPermissionTo('update_' . Str::plural(Str::snake(class_basename($model))));
    }

    public static function canDelete(Model $record): bool
    {
        $model = static::getModel();
        return auth()->user()->hasPermissionTo('delete_' . Str::plural(Str::snake(class_basename($model))));
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