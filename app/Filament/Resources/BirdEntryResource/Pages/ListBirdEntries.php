<?php

namespace App\Filament\Resources\BirdEntryResource\Pages;

use App\Filament\Resources\BirdEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBirdEntries extends ListRecords
{
    protected static string $resource = BirdEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
