<?php

namespace App\Filament\Resources\HazardReportResource\Pages;

use App\Filament\Resources\HazardReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHazardReport extends CreateRecord
{
    protected static string $resource = HazardReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reporter_name'] = auth()->user()->name;
        $data['reporter_mobile'] = auth()->user()->contact_number;
        $data['reporter_email'] = auth()->user()->email;
        $data['airport'] = auth()->user()->airport;

        return $data;
    }
}
