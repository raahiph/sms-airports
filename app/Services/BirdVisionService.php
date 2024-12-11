<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class BirdVisionService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
    }

    public function analyzeImage(UploadedFile $image)
    {
        try {
            // Convert image to base64
            $base64Image = base64_encode(file_get_contents($image->path()));

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->baseUrl, [
                'model' => 'claude-3-opus-20240229',
                'max_tokens' => 1024,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Please analyze this bird image and provide detailed information in this exact format easier to extract and parse:
                                - Common English Name:
                                - Local Name (Dhivehi):
                                - Scientific Name:
                                - Species Family:
                                - Mass/Weight:
                                - Average Flight Speed:
                                - Length:
                                - Wingspan:
                                - Migratory Status (Yes/No)):
                                - Typical Habitat:
                                - Diet:
                                - Physical Appearance:
                                - Flocking Behavior (Yes/No):",
                            ],
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $image->getMimeType(),
                                    'data' => $base64Image,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to analyze image: ' . $response->body());
            }

            $content = $response->json('content')[0]['text'] ?? null;
            return $this->parseResponse($content);

        } catch (\Exception $e) {
            Log::error('Bird Image Analysis Error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function parseResponse($content)
{
    // Split the content into lines
    $lines = explode("\n", $content);
    $data = [];
    $currentKey = null;
    $currentValue = '';

    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines
        if (empty($line)) {
            continue;
        }
        
        // Check if line contains a key indicator
        if (strpos($line, '-') === 0 && strpos($line, ':') !== false) {
            // If we have a previous key-value pair, save it
            if ($currentKey !== null) {
                $data[$currentKey] = trim($currentValue);
            }
            
            // Extract new key and start of value
            [$key, $value] = explode(':', $line, 2);
            
            // Normalize the key
            $key = trim(str_replace('-', '', $key));
            $key = strtolower($key);
            
            // Direct mapping for specific fields
            switch ($key) {
                case 'average flight speed':
                    $currentKey = 'flight_speed';
                    break;
                case 'mass/weight':
                case 'mass / weight':
                case 'typical mass/weight range':
                case 'mass weight':
                    $currentKey = 'mass_weight';
                    break;
                case 'local name (dhivehi)':
                    $currentKey = 'local_name_dhivehi';
                    break;
                default:
                    $currentKey = str_replace(' ', '_', $key);
                    break;
            }
            
            $currentValue = trim($value);
        } else {
            // Append to current value if it's a continuation
            if ($currentKey !== null && !empty($line)) {
                $currentValue .= ' ' . $line;
            }
        }
    }
    
    // Add the last key-value pair
    if ($currentKey !== null) {
        $data[$currentKey] = trim($currentValue);
    }

    // Debug log to check the mappings
    Log::info('Bird Analysis Keys', [
        'mapped_keys' => array_keys($data),
        'raw_content' => $content
    ]);

    return $data;
}
}