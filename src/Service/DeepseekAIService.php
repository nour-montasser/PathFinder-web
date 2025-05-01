<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepseekAIService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $deepseekApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $deepseekApiKey;
    }

    public function generateResponse(string $prompt): string
{
    try {
        $response = $this->httpClient->request('POST', 'https://api-inference.huggingface.co/models/deepseek-ai/DeepSeek-R1-Distill-Qwen-32B', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'inputs' => $prompt,
                'parameters' => [
                    'max_new_tokens' => 500,
                    'temperature' => 0.7,
                ]
            ],
        ]);

        $data = $response->toArray();
        return $data[0]['generated_text'] ?? 'Désolé, je n\'ai pas pu générer de réponse.';
        
    } catch (\Exception $e) {
        return 'Erreur API: ' . $e->getMessage();
    }
}
}