<?php

namespace App\Filament\Resources\HazardReportResource\Pages;

use App\Filament\Resources\HazardReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazardReports extends ListRecords
{
    protected static string $resource = HazardReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
