<?php

namespace App\Traits;

use App\Models\HazardReport;
use App\Models\RiskAssessment;
use App\Services\ClaudeAssessmentService;
use App\Filament\Resources\RiskAssessmentResource;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

trait GeneratesAiAssessment
{
    protected static function generateAiAssessment(HazardReport $record)
    {
        try {
            $aiService = app(ClaudeAssessmentService::class);
            
            Notification::make('generating')
                ->title('Generating Assessment')
                ->body('Please wait while the AI analyzes the hazard...')
                ->info()
                ->persistent()
                ->send();

            $assessmentData = $aiService->generateAssessment($record);
            
            $riskAssessment = RiskAssessment::create([
                'hazard_report_id' => $record->id,
                'executive_summary' => $assessmentData['executive_summary'],
                'risk_analysis' => $assessmentData['risk_analysis'],
                'impact_assessment' => $assessmentData['impact_assessment'],
                'mitigation_measures' => $assessmentData['mitigation_measures'],
                'implementation_timeline' => $assessmentData['implementation_timeline'],
                'monitoring_requirements' => $assessmentData['monitoring_requirements'],
                'generated_by' => 'Claude AI Assistant',
                'status' => 'generated',
                'generated_at' => now(),
            ]);

            Notification::make('generated')
                ->title('Risk Assessment Generated Successfully')
                ->success()
                ->send();

            return redirect()->to(RiskAssessmentResource::getUrl('view', ['record' => $riskAssessment]));
        } catch (\Exception $e) {
            Log::error('Risk Assessment Generation Error', [
                'error' => $e->getMessage(),
                'hazard_id' => $record->id
            ]);
            
            Notification::make('error')
                ->title('Error Generating Assessment')
                ->body('An error occurred. Please try again later.')
                ->danger()
                ->send();
        }
    }
}