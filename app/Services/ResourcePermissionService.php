<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ResourcePermissionService
{
    public function getResourcePermissions(): array
    {
        $permissions = [];
        $resourcePath = app_path('Filament/Resources');
        
        // Get all resource files
        $files = File::allFiles($resourcePath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $className = 'App\\Filament\\Resources\\' . $file->getFilenameWithoutExtension();
                
                if (class_exists($className)) {
                    // Get the model name from the resource
                    $modelName = $this->getModelNameFromResource($className);
                    if ($modelName) {
                        // Generate permissions for this resource
                        $permissions = array_merge(
                            $permissions,
                            $this->generatePermissionsForResource($modelName)
                        );
                    }
                }
            }
        }
        
        // Add any additional custom permissions
        $permissions = array_merge(
            $permissions,
            $this->getCustomPermissions()
        );
        
        return array_unique($permissions);
    }

    protected function getModelNameFromResource(string $resourceClass): ?string
    {
        if (!class_exists($resourceClass)) {
            return null;
        }

        // Get the static model property
        $model = $resourceClass::getModel();
        
        // Convert to snake case and pluralize
        return Str::snake(Str::pluralStudly(class_basename($model)));
    }

    protected function generatePermissionsForResource(string $modelName): array
    {
        return [
            "view_{$modelName}",
            "view_any_{$modelName}",
            "create_{$modelName}",
            "update_{$modelName}",
            "delete_{$modelName}",
            "delete_any_{$modelName}",
            "force_delete_{$modelName}",
            "force_delete_any_{$modelName}",
            "restore_{$modelName}",
            "restore_any_{$modelName}",
        ];
    }

    protected function getCustomPermissions(): array
    {
        return [
            'access_filament',
            'view_admin',
            'manage_settings',
        ];
    }
}