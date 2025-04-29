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
                        'max_new_tokens' => 100,
                        'temperature' => 0.7,
                    ]
                ],
            ]);
    
            // Log the full response for debugging
            $data = $response->toArray();
            if (isset($data[0]['generated_text'])) {
                return $data[0]['generated_text'];
            }
            // Log error if no valid response is returned
            error_log('API response error: ' . print_r($data, true));
            return 'Désolé, je n\'ai pas pu générer de réponse.';
            
        } catch (\Exception $e) {
            // Log exception error
            error_log('Error in Deepseek API call: ' . $e->getMessage());
            return 'Erreur API: ' . $e->getMessage();
        }
    }
    
}