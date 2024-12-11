<?php

namespace App\Filament\Resources\BirdEntryResource\Pages;

use App\Filament\Resources\BirdEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBirdEntry extends CreateRecord
{
    protected static string $resource = BirdEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['entered_by'] = auth()->user()->name;
        
        return $data;
    }
}
