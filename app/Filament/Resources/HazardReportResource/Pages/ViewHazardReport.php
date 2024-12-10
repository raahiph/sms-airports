<?php

namespace App\Filament\Resources\HazardReportResource\Pages;

use App\Filament\Resources\HazardReportResource;
use App\Filament\Resources\RiskAssessmentResource;
use Filament\Resources\Pages\ViewRecord;
use App\Services\AiAssessmentService;
use App\Models\RiskAssessment;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Traits\GeneratesAiAssessment;

class ViewHazardReport extends ViewRecord
{
    protected static string $resource = HazardReportResource::class;

    use GeneratesAiAssessment;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateAssessment')
                ->label('Generate AI Assessment')
                ->icon('heroicon-m-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Generate AI Risk Assessment')
                ->modalDescription('This will analyze the hazard and generate a detailed risk assessment using AI. Continue?')
                ->modalSubmitActionLabel('Generate')
                ->action(fn () => static::generateAiAssessment($this->record)),
        ];
    }
}