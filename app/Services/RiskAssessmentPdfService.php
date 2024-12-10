<?php

namespace App\Services;

use App\Models\RiskAssessment;
use Barryvdh\DomPDF\Facade\Pdf;

class RiskAssessmentPdfService
{
    public function generatePdf(RiskAssessment $riskAssessment)
    {
        $data = [
            'assessment' => [
                'executive_summary' => $riskAssessment->executive_summary,
                'risk_analysis' => $riskAssessment->risk_analysis,
                'impact_assessment' => $riskAssessment->impact_assessment,
                'mitigation_measures' => $riskAssessment->mitigation_measures,
                'implementation_timeline' => $riskAssessment->implementation_timeline,
                'monitoring_requirements' => $riskAssessment->monitoring_requirements,
                'generated_by' => $riskAssessment->generated_by,
                'status' => $riskAssessment->status,
                'generated_at' => $riskAssessment->generated_at->format('d/m/Y H:i'),
            ],
            'hazard' => [
                'hazard_location' => $riskAssessment->hazardReport->hazard_location,
                'hazard_description' => $riskAssessment->hazardReport->hazard_description,
                'risk_rating' => $riskAssessment->hazardReport->risk_rating,
                'risk_level' => $riskAssessment->hazardReport->risk_level,
            ],
            'date' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('pdfs.risk-assessment', $data);
        return $pdf->download("risk-assessment-{$riskAssessment->id}.pdf");
    }
}