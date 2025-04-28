<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\JobOfferRepository;
use App\Repository\ApplicationJobRepository;
use App\Entity\App_user;
use App\Entity\Cv;
use Symfony\Component\Routing\Annotation\Route;

class JobSuggestionController extends BaseController
{
    private ApplicationJobRepository $applicationJobRepository;
    private JobOfferRepository $jobOfferRepository;

    public function __construct(ApplicationJobRepository $applicationJobRepository, JobOfferRepository $jobOfferRepository)
    {
        $this->applicationJobRepository = $applicationJobRepository;
        $this->jobOfferRepository = $jobOfferRepository;
    }


    private function getUserLatestCv(App_user $user): ?Cv
    {
        $cvs = $user->getCvs();
        if ($cvs->isEmpty()) {
            return null;
        }

        $latestCv = null;
        $latestDate = null;

        foreach ($cvs as $cv) {
            if ($latestDate === null || $cv->getDate_creation() > $latestDate) {
                $latestDate = $cv->getDate_creation();
                $latestCv = $cv;
            }
        }

        return $latestCv;
    }

    private function calculateSkillsMatch(string $userSkills, string $jobSkills): float
    {
        $userSkillsArray = array_map('trim', explode(',', strtolower($userSkills)));
        $jobSkillsArray = array_map('trim', explode(',', strtolower($jobSkills)));

        if (empty($jobSkillsArray)) {
            return 0;
        }

        $matchingSkills = array_intersect($userSkillsArray, $jobSkillsArray);
        return count($matchingSkills) / count($jobSkillsArray);
    }

    private function calculateTitleSimilarity(string $userTitle, string $jobTitle): float
    {
        if (empty($userTitle) || empty($jobTitle)) {
            return 0;
        }

        // Convert to lowercase and split into words
        $userWords = explode(' ', strtolower($userTitle));
        $jobWords = explode(' ', strtolower($jobTitle));

        // Remove common stop words
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'with', 'by'];
        $userWords = array_diff($userWords, $stopWords);
        $jobWords = array_diff($jobWords, $stopWords);

        if (empty($userWords) || empty($jobWords)) {
            return 0;
        }

        // Count matching words
        $commonWords = array_intersect($userWords, $jobWords);
        $similarity = count($commonWords) / max(count($userWords), count($jobWords));

        // Boost similarity if there's at least one matching word
        return min(1, $similarity * 1.5);
    }

    private function getSuggestionReason(float $skillsMatch, float $titleSimilarity): string
    {
        if ($skillsMatch > 0.8 && $titleSimilarity > 0.7) {
            return 'Excellent match for your skills and experience';
        } elseif ($skillsMatch > 0.6) {
            return 'Strong skills match';
        } elseif ($titleSimilarity > 0.7) {
            return 'Highly relevant to your current position';
        } else {
            return 'Good potential opportunity based on your profile';
        }
    }

    // In JobSuggestionController.php

    public function getSuggestionsForUser(App_user $user): array
    {
        $latestCv = $this->getUserLatestCv($user);
        if (!$latestCv) {
            return [];
        }

        $jobOffers = $this->jobOfferRepository->findActiveOffers();

        $suggestions = [];

        foreach ($jobOffers as $jobOffer) {
            // Skip conditions
            if ($jobOffer->getUser()->getId_user() === $user->getId_user()) {
                continue;
            }

            if ($this->applicationJobRepository->findUserApplicationForJob($user->getId_user(), $jobOffer->getIdOffer())) {
                continue;
            }

            $skillsMatch = $this->calculateSkillsMatch(
                $latestCv->getSkills(),
                $jobOffer->getSkills()
            );

            $titleSimilarity = $this->calculateTitleSimilarity(
                $latestCv->getUser_title(),
                $jobOffer->getTitle()
            );

            $previousApplications = $this->applicationJobRepository->countUserApplicationsForCompany(
                $user->getId_user(),
                $jobOffer->getUser()->getId_user()
            );

            $command = 'cd C:/xampp/htdocs/PathFinder && ' .
                'call venv/Scripts/activate && ' .
                'python ml/predict_script.py ' .
                escapeshellarg($skillsMatch) . ' ' .
                escapeshellarg($titleSimilarity) . ' ' .
                escapeshellarg($previousApplications) . ' 2>&1';

            $prediction = shell_exec($command);

            if ($prediction === null) {
                throw new \RuntimeException('Python script returned null. Check permissions and paths.');
            }

            // Clean the output
            $probability = (float)trim($prediction);

            if ($prediction !== null) {
                $probability = (float)trim($prediction);

                if ($probability > 0.5) {
                    $suggestions[] = [
                        'job' => $jobOffer,
                        'confidence' => $probability * 100,
                        'reason' => $this->getSuggestionReason($skillsMatch, $titleSimilarity),
                        'skillsMatch' => $skillsMatch,
                        'titleSimilarity' => $titleSimilarity,
                    ];
                }
            }
        }

        return $suggestions;
    }
}
