<?php

namespace App\Filament\Resources\RiskAssessmentResource\Pages;

use App\Filament\Resources\RiskAssessmentResource;
use App\Services\RiskAssessmentPdfService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewRiskAssessment extends ViewRecord
{
    protected static string $resource = RiskAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // $pdfService = app(RiskAssessmentPdfService::class);
                    // return $pdfService->generatePdf($this->record);
                }),
            Actions\Action::make('changeStatus')
                ->label('Change Status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->options([
                            'generated' => 'Generated',
                            'reviewed' => 'Reviewed',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected'
                        ])
                        ->required()
                ])
                ->action(function (array $data) {
                    $this->record->update(['status' => $data['status']]);
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // You can add widgets here if needed
        ];
    }
}