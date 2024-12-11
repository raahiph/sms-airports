<?php

namespace App\Filament\Resources\OccurrenceResource\Pages;

use App\Filament\Resources\OccurrenceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOccurrence extends CreateRecord
{
    protected static string $resource = OccurrenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reporter_name'] = auth()->user()->name;
        $data['reporter_mobile'] = auth()->user()->contact_number;
        $data['reporter_email'] = auth()->user()->email;
        $data['airport'] = auth()->user()->airport;

        return $data;
    }
}
