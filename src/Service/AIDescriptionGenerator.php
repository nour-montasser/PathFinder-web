<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class AIDescriptionGenerator
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiUrl;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        string $deepseekApiKey,
        LoggerInterface $logger,
        string $apiUrl
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = trim($deepseekApiKey); // Ensure no whitespace in the key
        $this->logger = $logger;
        $this->apiUrl = 'https://api-inference.huggingface.co/models/deepseek-ai/DeepSeek-R1-Distill-Qwen-32B';
    }

    public function generateDescription(string $serviceTitle): ?string
    {
        if (empty($serviceTitle)) {
            throw new \InvalidArgumentException('Service title cannot be empty');
        }

        $prompt = $this->buildPrompt($serviceTitle);

        try {
            // Attempt to make the API request
           
            $response = $this->makeApiRequest($prompt);
            $generatedDescription = $this->processApiResponse($serviceTitle, $response);

            if (empty($generatedDescription)) {
                $this->logger->warning('Generated description is empty.');
               
                throw new \RuntimeException('Generated description is empty.');
            }

            return $generatedDescription;
        } catch (\Exception $e) {
            $this->logger->error('AI Description Generation failed', [
                'error' => $e->getMessage(),
                'serviceTitle' => $serviceTitle
            ]);
            return null;  // Return null if an error occurs
        }
       

    }

    private function buildPrompt(string $serviceTitle): string
    {
        return sprintf(
            "Write a compelling job description for a freelance %s service. " .
            "The description should be clear, specific, and directly highlight " .
            "the skills, experience, and qualities required. The response should " .
            "be structured as a professional job posting, starting with 'I'm looking for' " .
            "and limited to 4-5 sentences.",
            $serviceTitle
        );
    }

    private function makeApiRequest(string $prompt): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Hugging Face API key is not configured');
        }

        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,  // Add the API key in Authorization header
                    'Content-Type' => 'application/json',
                ],
                'json' => ['inputs' => $prompt],
                'timeout' => 30,  // Timeout for the request in seconds
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                // More specific error message for authentication failure
                throw new \RuntimeException(
                    'Hugging Face API authentication failed. Please verify your API key is valid ' .
                    'and has permissions for this model.'
                );
            }

            if ($statusCode !== Response::HTTP_OK) {
                throw new \RuntimeException(
                    sprintf('API request failed with status code %d', $statusCode)
                );
            }

            return $response->toArray();  // Return the parsed response as an array
        } catch (\Exception $e) {
            $this->logger->error('Hugging Face API request failed', [
                'error' => $e->getMessage(),
                'model' => 'DeepSeek-R1-Distill-Qwen-32B'
            ]);
            throw $e;  // Rethrow the exception
        }
    }

    private function processApiResponse(string $serviceTitle, array $apiResponse): string
    {
        if (!isset($apiResponse[0]['generated_text'])) {
            $this->logger->error('Invalid response format from Deepseek API.');
            throw new \RuntimeException('Invalid API response format');
        }

        $generatedText = trim($apiResponse[0]['generated_text']);
        return $this->cleanResponse($serviceTitle, $generatedText);
    }

    private function cleanResponse(string $serviceTitle, string $responseText): string
    {
        // Remove the prompt if it's included in the response
        $prompt = $this->buildPrompt($serviceTitle);
        $responseText = str_ireplace($prompt, '', $responseText);

        // Find the actual response start
        $startPos = stripos($responseText, "I'm looking for");
        if ($startPos !== false) {
            $responseText = substr($responseText, $startPos);
        } else {
            // If "I'm looking for" is not found, we can append a fallback message
            $responseText = sprintf("I'm looking for a %s expert.", $serviceTitle);
        }

        // Clean up formatting artifacts (such as markdown or HTML tags)
        $responseText = preg_replace('/\*{2,}/', '', $responseText);  // Remove bold markers
        $responseText = preg_replace('/<.*?>/', '', $responseText);  // Remove HTML tags
        $responseText = preg_replace('/\s+/', ' ', $responseText);   // Normalize whitespace

        return trim($responseText);
    }

    public function generatePriceEstimation(string $title, string $description, string $field, string $experienceLevel): ?float
    {
        try {
            $prompt = sprintf(
                "Estimate the total freelance price( not per hour) for a '%s' service in the '%s' field with %s experience. Consider this description: %s. Return only a number between 50 and 10000.",
                $title,
                $field,
                $experienceLevel,
                $description
            );
    
            $response = $this->makeApiRequest($prompt); // Same as you do for description
    
            if (!$response || !isset($response[0]['generated_text'])) {
                return null;
            }
    
            $text = $response[0]['generated_text'];
            preg_match_all('/\d+/', $text, $matches);
    
            $numbers = array_map('intval', $matches[0]);
            $validPrices = array_filter($numbers, fn($num) => $num >= 50 && $num <= 10000);
    
            return count($validPrices) ? (float) end($validPrices) : null;
    
        } catch (\Exception $e) {
            $this->logger->error('AI Price Generation Failed: '.$e->getMessage());
            return null;
        }
    }
    
private function buildPriceEstimationPrompt(string $serviceTitle, string $description, string $field, string $experienceLevel): string
{
    return sprintf(
        "Estimate the market price for a freelance %s service. The description of the service is: %s. " .
        "The service falls under the field of %s and requires %s level experience. Provide a price range for this service in the market.",
        $serviceTitle, $description, $field, $experienceLevel
    );
}


   private function processPriceEstimationResponse(array $apiResponse): string
{
    if (!isset($apiResponse[0]['generated_text'])) {
        $this->logger->error('Invalid response format from Deepseek API', $apiResponse);
        throw new \RuntimeException('Invalid API response format for price estimation');
    }

    $responseText = trim($apiResponse[0]['generated_text']);
    
    // Extract the first numeric value found in the response
    if (preg_match('/\b(\d+)\b/', $responseText, $matches)) {
        return $matches[1];
    }
    
    // Fallback to a default value if no number found
    $this->logger->warning('No numeric price found in API response, using fallback');
    return '100'; // Default fallback price
}

}
