<?php

namespace App\Filament\Resources\HazardReportResource\Pages;

use App\Filament\Resources\HazardReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazardReport extends EditRecord
{
    protected static string $resource = HazardReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
