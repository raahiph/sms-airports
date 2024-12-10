<?php

namespace App\Filament\Resources\RiskAssessmentResource\Pages;

use App\Filament\Resources\RiskAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiskAssessment extends EditRecord
{
    protected static string $resource = RiskAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
