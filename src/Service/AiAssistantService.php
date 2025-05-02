<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiAssistantService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $openrouterApiKey)
    {
        $this->client = $client;
        $this->apiKey = $openrouterApiKey;
    }

    public function askQuestion(string $prompt): ?string
    {
        try {
            $response = $this->client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'mistralai/mistral-7b-instruct', // ✅ simple model
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]
            ]);

            $data = $response->toArray(false);

            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }

            return '⚠️ No response generated.';
        } catch (\Exception $e) {
            return '🚨 Error: ' . $e->getMessage();
        }
    }
}
