<?php

namespace App\Service;

use App\Entity\Cv;
use App\Entity\Job_offer;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AiCoverLetterGenerator
{
    private HttpClientInterface $httpClient;
    private string $huggingFaceToken;
    private string $modelName;

    public function __construct(
        HttpClientInterface $httpClient,
        string $huggingFaceToken,
        string $modelName = 'facebook/blenderbot-400M-distill'
    ) {
        $this->httpClient = $httpClient;
        $this->huggingFaceToken = $huggingFaceToken;
        $this->modelName = $modelName;
    }

    public function generateCoverLetter(Cv $cv, Job_offer $jobOffer): array
    {
        $prompt = $this->preparePrompt($cv, $jobOffer);
        $response = $this->callApi($prompt);
        
        return $this->parseResponse($response);
    }


    private function preparePrompt(Cv $cv, Job_offer $jobOffer): string
{
    $applicant = $cv->getUser();
    $applicantProfile = $applicant->getProfile();
    $company = $jobOffer->getUser();
    $companyProfile = $company->getProfile();

    // Build applicant details
    $applicantDetails = [];
    if ($applicant->getName()) $applicantDetails[] = "- Name: {$applicant->getName()}";
    if ($cv->getUserTitle()) $applicantDetails[] = "- Professional Title: {$cv->getUserTitle()}";
    if ($applicantProfile && $applicantProfile->getCurrentOccupation()) {
        $applicantDetails[] = "- Current Role: {$applicantProfile->getCurrentOccupation()}";
    }
    if ($cv->getIntroduction()) $applicantDetails[] = "- Summary: {$cv->getIntroduction()}";
    if ($cv->getSkills()) $applicantDetails[] = "- Key Skills: {$cv->getSkills()}";
    if ($applicantProfile && $applicantProfile->getPhone()) {
        $applicantDetails[] = "- Contact: {$applicantProfile->getPhone()}";
    }

    // Build company details
    $companyDetails = [];
    if ($company->getName()) $companyDetails[] = "- Company: {$company->getName()}";
    if ($companyProfile && $companyProfile->getCurrentOccupation()) {
        $companyDetails[] = "- Industry: {$companyProfile->getCurrentOccupation()}";
    }
    if ($jobOffer->getTitle()) $companyDetails[] = "- Position: {$jobOffer->getTitle()}";

    // Format experiences (only if they exist)
    $experiences = [];
    if (!$cv->getExperiences()->isEmpty()) {
        $experiences = array_map(
            fn($exp) => sprintf(
                "- %s at %s (%s to %s): %s",
                $exp->getPosition(),
                $exp->getLocationName(),
                $exp->getStartDate()->format('Y'),
                $exp->getEndDate()->format('Y'),
                $exp->getDescription()
            ),
            $cv->getExperiences()->toArray()
        );
    }

    // Format certificates (only if they exist)
    $certificates = [];
    if (!$cv->getCertificates()->isEmpty()) {
        $certificates = array_map(
            fn($cert) => sprintf(
                "- %s from %s (issued %s)",
                $cert->getTitle(),
                $cert->getIssuedBy(),
                $cert->getIssuedate()->format('Y')
            ),
            $cv->getCertificates()->toArray()
        );
    }

    // Format languages (only if they exist)
    $languages = [];
    if (!$cv->getLanguages()->isEmpty()) {
        $languages = array_map(
            fn($lang) => sprintf("- %s (%s)", $lang->getLanguagename(), $lang->getLevel()),
            $cv->getLanguages()->toArray()
        );
    }

    $promptParts = [
        "Write a professional cover letter for a job application with these requirements:",
        "1. Start with: 'Subject: Application for {$jobOffer->getTitle()} Position'",
        "2. Structure:",
        "   - First paragraph: Express interest in the position",
        "   - Second paragraph: Highlight relevant skills/experience",
        "   - Third paragraph: Showcase achievements/certifications",
        "   - Closing paragraph: Conclude with enthusiasm",
        "3. Use proper business letter formatting with paragraphs",
        "4. Keep it concise (3-4 paragraphs)",
        "5. Only include information that is available below:"
    ];

    // Add sections only if they have content
    if (!empty($applicantDetails)) {
        $promptParts[] = "\nApplicant Details:";
        $promptParts[] = implode("\n", $applicantDetails);
    }

    if (!empty($companyDetails)) {
        $promptParts[] = "\nCompany/Position Details:";
        $promptParts[] = implode("\n", $companyDetails);
    }

    if (!empty($experiences)) {
        $promptParts[] = "\nProfessional Experience:";
        $promptParts[] = implode("\n", $experiences);
    }

    if (!empty($certificates)) {
        $promptParts[] = "\nCertifications:";
        $promptParts[] = implode("\n", $certificates);
    }

    if (!empty($languages)) {
        $promptParts[] = "\nLanguages:";
        $promptParts[] = implode("\n", $languages);
    }

    $promptParts[] = "\nImportant Notes:";
    $promptParts[] = "- Do NOT include any placeholders or sections you don't have information for";
    $promptParts[] = "- Address the hiring manager professionally";
    $promptParts[] = "- Keep the tone positive and professional";
    $promptParts[] = "- Use real data only, don't invent information";

    return implode("\n", $promptParts);
}
    
    

private function parseResponse(string $response): array
{
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException('Invalid response from AI service');
    }

    $generatedText = $data[0]['generated_text'] ?? $this->generateFallbackContent();

    // Ensure proper line breaks
    $generatedText = nl2br($generatedText);

    // Extract subject if it exists
    $subject = 'Application for Position';
    if (preg_match('/Subject:\s*(.+?)\n\n/', $generatedText, $matches)) {
        $subject = trim($matches[1]);
        $generatedText = str_replace($matches[0], '', $generatedText);
    }

    // Format the content with proper paragraphs
    $paragraphs = explode("\n\n", trim($generatedText));
    $formattedContent = implode("\n\n", array_map('trim', $paragraphs));

    return [
        'subject' => $subject,
        'content' => $formattedContent
    ];
}

private function generateFallbackContent(): string
{
    return "Subject: Application for Position\n\n"
         . "Dear Hiring Manager,\n\n"
         . "I am excited to apply for this position. My skills and experience make me a strong candidate.\n\n"
         . "Sincerely,\nApplicant";
}
    private function callApi(string $prompt): string
{
    try {
        $response = $this->httpClient->request(
            'POST',
            "https://api-inference.huggingface.co/models/{$this->modelName}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->huggingFaceToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_length' => 400,
                        'temperature' => 0.7,
                        'do_sample' => true,
                        'return_full_text' => false
                    ]
                ],
                'timeout' => 30
            ]
        );

        $content = $response->getContent(false); // Get raw content

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $errorData = json_decode($content, true);
            throw new HttpException(
                $response->getStatusCode(),
                "AI Service Error: " . ($errorData['error'] ?? 'Unknown error')
            );
        }

        return $content;
    } catch (\Exception $e) {
        throw new HttpException(
            Response::HTTP_SERVICE_UNAVAILABLE,
            "Failed to generate cover letter: " . $e->getMessage()
        );
    }
}

   
}