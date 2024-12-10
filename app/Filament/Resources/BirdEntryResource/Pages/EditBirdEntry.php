<?php

namespace App\Filament\Resources\BirdEntryResource\Pages;

use App\Filament\Resources\BirdEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBirdEntry extends EditRecord
{
    protected static string $resource = BirdEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
