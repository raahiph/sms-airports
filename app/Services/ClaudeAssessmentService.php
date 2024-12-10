<?php

namespace App\Services;

use App\Models\HazardReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ClaudeAssessmentService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.anthropic.com/v1/messages';
    private int $timeout = 120; // 2 minutes timeout
    private int $retries = 3;   // Number of retries

    public function __construct()
    {
        $apiKey = config('services.anthropic.api_key');
                
        if (empty($apiKey)) {
            throw new RuntimeException(
                'Claude API key is not configured. Please add ANTHROPIC_API_KEY to your .env file.'
            );
        }
        
        $this->apiKey = $apiKey;
    }

    public function generateAssessment(HazardReport $hazardReport): array
    {
        try {
            Log::info('Starting Claude API request', [
                'hazard_id' => $hazardReport->id,
            ]);

            $response = Http::timeout($this->timeout)
                ->retry($this->retries, 5000) // Retry 3 times with 5 second delay
                ->withHeaders([
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                ])
                ->withOptions([
                    'connect_timeout' => 30,
                ])
                ->post($this->baseUrl, [
                    'model' => 'claude-3-opus-20240229',
                    'max_tokens' => 4000,
                    'system' => $this->getSystemPrompt(),
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($hazardReport)
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                $errorMessage = $response->body();
                Log::error('Claude API Error Response', [
                    'status' => $response->status(),
                    'body' => $errorMessage,
                    'headers' => $response->headers()
                ]);
                throw new RuntimeException('Claude API Error: ' . $errorMessage);
            }

            $content = $response->json('content')[0]['text'] ?? null;

            if (!$content) {
                throw new RuntimeException('No content in Claude response');
            }

            return $this->parseResponse($content);

        } catch (\Exception $e) {
            Log::error('Claude Assessment Generation Error', [
                'error' => $e->getMessage(),
                'hazard_id' => $hazardReport->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw with more user-friendly message
            throw new RuntimeException(
                'Unable to generate assessment at this time. Please try again in a few moments. ' .
                'If the problem persists, please contact support.'
            );
        }
    }

    private function getSystemPrompt(): string
    {
        return "You are an expert aviation safety analyst with extensive experience in risk assessment and hazard analysis. 
                Generate a structured report with these exact sections: Executive Summary, Risk Analysis, Impact Assessment, 
                Mitigation Measures, Implementation Timeline, and Monitoring Requirements.";
    }

    private function buildPrompt(HazardReport $hazardReport): string
    {
        return <<<EOT
Please analyze this airport hazard and provide a risk assessment report:

HAZARD DETAILS:
Location: {$hazardReport->hazard_location}
Description: {$hazardReport->hazard_description}
Current Risk Rating: {$hazardReport->risk_rating}
Current Corrective Actions: {$hazardReport->corrective_actions}

Format your response with these exact sections:
1. Executive Summary:
2. Risk Analysis:
3. Impact Assessment:
4. Mitigation Measures:
5. Implementation Timeline:
6. Monitoring Requirements:
EOT;
    }

    private function parseResponse(string $response): array
    {
        $sections = [
            'executive_summary' => '',
            'risk_analysis' => '',
            'impact_assessment' => '',
            'mitigation_measures' => '',
            'implementation_timeline' => '',
            'monitoring_requirements' => ''
        ];

        // Log the raw response for debugging
        Log::info('Raw Claude Response', ['response' => $response]);

        // More robust section extraction
        $patterns = [
            'executive_summary' => '/Executive Summary:?(.*?)(?=Risk Analysis:|$)/si',
            'risk_analysis' => '/Risk Analysis:?(.*?)(?=Impact Assessment:|$)/si',
            'impact_assessment' => '/Impact Assessment:?(.*?)(?=Mitigation Measures:|$)/si',
            'mitigation_measures' => '/Mitigation Measures:?(.*?)(?=Implementation Timeline:|$)/si',
            'implementation_timeline' => '/Implementation Timeline:?(.*?)(?=Monitoring Requirements:|$)/si',
            'monitoring_requirements' => '/Monitoring Requirements:?(.*?)$/si'
        ];

        foreach ($patterns as $section => $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $sections[$section] = trim($matches[1] ?? '');
                Log::info("Parsed section: {$section}", ['content' => $sections[$section]]);
            } else {
                Log::warning("Failed to parse section: {$section}");
            }
        }

        return $sections;
    }
}