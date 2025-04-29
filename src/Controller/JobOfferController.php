<?php

namespace App\Controller;

use App\Entity\ApplicationJob;
use App\Entity\Job_offer;
use App\Form\JobOfferType;
use App\Repository\ApplicationJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\JobOfferRepository;
use App\Entity\Skilltest;
use App\Form\SkilltestType;
use App\Service\GeonamesService;
use Symfony\Component\Form\FormError;

#[Route('/jobOffer')]
final class JobOfferController extends BaseController
{
    #[Route(name: 'app_job_offer_index', methods: ['GET'])]
    public function index(
        JobOfferRepository $repository,
        Request $request,
        ApplicationJobRepository $applicationJobRepository,
        JobSuggestionController $suggestionController
    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
        
        
        // Get suggestions (always get all recommendations)
        $suggestions = [];
        if ($user->getRole() === 2) {
            $suggestions = $suggestionController->getSuggestionsForUser($user);
        }
    
        // Only fetch regular job offers if not in recommendations-only view
        $jobOffers = [];
        $hasApplied = [];
        $recentJobs = [];
        $searchTerm = '';
        $filters = ['types' => [], 'fields' => [], 'education' => []];
        
            $searchTerm = trim($request->query->get('search', ''));
            $filters = [
                'types' => $request->query->all('types') ?? [],
                'fields' => $request->query->all('fields') ?? [],
                'education' => $request->query->all('education') ?? []
            ];
            
            $filterUser = ($user->getRole() === 1) ? $user : null;
            $locationFilter = null;
            
            if ($request->query->has('location')) {
                $locationFilter = [
                    'city' => $request->query->get('location'),
                    'radius' => $request->query->get('locationType') === 'nearby' 
                        ? (int)$request->query->get('distance', 50) 
                        : 0
                ];
            }
            
            $jobOffers = $repository->findWithLocationFilter(
                $searchTerm,
                $filters,
                $filterUser,
                $locationFilter
            );
            
            if ($user->getRole() === 2) {
                foreach ($jobOffers as $jobOffer) {
                    $application = $applicationJobRepository->findUserApplicationForJob(
                        $user->getId_user(),
                        $jobOffer->getIdOffer()
                    );
                    if ($application) {
                        $hasApplied[$jobOffer->getIdOffer()] = $application;
                    }
                }
            }
    
            $recentJobs = $repository->findBy(
                $filterUser ? ['user' => $filterUser] : [],
                ['date_posted' => 'DESC'],
                5
            );
        
        
        return $this->render('job_offer/index.html.twig', [
            'job_offers' => $jobOffers,
            'search_term' => $searchTerm,
            'recent_jobs' => $recentJobs,
            'selected_types' => $filters['types'],
            'selected_fields' => $filters['fields'],
            'selected_education' => $filters['education'],
            'current_user' => $user,
            'has_applied' => $hasApplied,
            'is_ajax' => $request->isXmlHttpRequest() || $request->query->get('ajax'),
            'suggestions' => $suggestions,
            'show_only_recommended' => false
        ]);
    }

    #[Route('/new', name: 'app_job_offer_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    JobOfferRepository $jobOfferRepository,
    GeonamesService $geonamesService
): Response {
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $jobOffer = new Job_offer();
    $jobOffer->setDatePosted(new \DateTime("now"));
    $jobOffer->setUser($user);
    
    $countryMap = $geonamesService->fetchCountryMap();
    
    $form = $this->createForm(JobOfferType::class, $jobOffer, [
        'countries' => $countryMap
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Manually validate the city
        $countryCode = $form->get('country')->getData();
        $city = $form->get('city')->getData();
        
        if ($countryCode && $city) {
            try {
                $validCities = $geonamesService->fetchCities($countryCode);
                if (!in_array($city, $validCities)) {
                    $form->get('city')->addError(new FormError('Invalid city for selected country'));
                }
            } catch (\Exception $e) {
                $form->get('city')->addError(new FormError('Could not validate city'));
            }
        }
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $countryCode = $form->get('country')->getData();
        $countryName = array_search($countryCode, $countryMap);
        $city = $form->get('city')->getData();
        
        $jobOffer->setAddress("$countryName, $city");
        
        $entityManager->persist($jobOffer);
        $entityManager->flush();

        return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
    }

    $stats = $jobOfferRepository->getUserStats($user->getId_user());
    
    return $this->render('job_offer/new.html.twig', [
        'job_offer' => $jobOffer,
        'form' => $form,
        'stats' => $stats
    ]);
}
    
#[Route('/get-cities/{countryCode}', name: 'app_job_offer_cities', methods: ['GET'])]
public function getCities(string $countryCode, GeonamesService $geonamesService): JsonResponse
{
    try {
        $cities = $geonamesService->fetchCities($countryCode);
        return $this->json($cities);
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], 400);
    }
}

    #[Route('/{id_offer}', name: 'app_job_offer_show', methods: ['GET'])]
    public function show(
        Job_offer $jobOffer,
        ApplicationJobRepository $applicationJobRepository
    ): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
    
        $hasApplied = [];
        if ($user->getRole() === 2) {
            $application = $applicationJobRepository->findUserApplicationForJob(
                $user->getId_user(),
                $jobOffer->getIdOffer()
            );
        
            if ($application) {
                $hasApplied[$jobOffer->getIdOffer()] = $application;
            }
        }
    
        // Only create form if user owns the job offer
        $skilltestForm = null;
        if ($user->getId_user() === $jobOffer->getUser()->getId_user()) {
            $skilltest = new Skilltest();
            $skilltest->setJobOffer($jobOffer);
            $skilltestForm = $this->createForm(SkilltestType::class, $skilltest)->createView();
        }
    
        return $this->render('job_offer/show.html.twig', [
            'job_offer' => $jobOffer,
            'has_applied' => $hasApplied,
            'current_user' => $user,
            'skilltestForm' => $skilltestForm,
        ]);
    }
    
    #[Route('/{id_offer}/skilltest', name: 'app_job_offer_skilltest_create', methods: ['POST'])]
    public function createSkillTest(
        Request $request,
        Job_offer $jobOffer,
        EntityManagerInterface $entityManager
    ): Response {
         $skilltest = new Skilltest();
    $skilltest->setJobOffer($jobOffer); // Set the job offer before handling the form

    $form = $this->createForm(SkilltestType::class, $skilltest);
    $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist questions
            foreach ($skilltest->getQuestions() as $question) {
                $question->setSkillTest($skilltest);
                $entityManager->persist($question);
            }
    
            $entityManager->persist($skilltest);
            $entityManager->flush();
    
            return $this->json([
                'success' => true,
                'message' => 'Skill test created successfully!'
            ]);
        }
    
        // If form is invalid
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        
        return $this->json([
            'success' => false,
            'errors' => $errors
        ], 400);
    }

    #[Route('/{id_offer}/edit', name: 'app_job_offer_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request, 
    Job_offer $jobOffer, 
    EntityManagerInterface $entityManager,
    GeonamesService $geonamesService
): Response {
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    if (!$user || $jobOffer->getUser() !== $user) {
        throw $this->createAccessDeniedException('You can only edit your own job offers');
    }

    // Parse existing address
    $addressParts = explode(', ', $jobOffer->getAddress() ?? '');
    $countryMap = $geonamesService->fetchCountryMap();
    
    $form = $this->createForm(JobOfferType::class, $jobOffer, [
        'countries' => $countryMap,
        'current_country' => $addressParts[0] ?? null,
        'current_city' => $addressParts[1] ?? null
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Rebuild address from form data
        $countryCode = $form->get('country')->getData();
        $countryName = array_search($countryCode, $countryMap);
        $city = $form->get('city')->getData();
        $jobOffer->setAddress("$countryName, $city");
        
        $entityManager->flush();

        return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('job_offer/edit.html.twig', [
        'job_offer' => $jobOffer,
        'form' => $form,
    ]);
}

    #[Route('/{id_offer}', name: 'app_job_offer_delete', methods: ['POST'])]
    public function delete(Request $request, Job_offer $jobOffer, EntityManagerInterface $entityManager): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        if (!$user || $jobOffer->getUser() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own job offers');
        }

        if ($this->isCsrfTokenValid('delete' . $jobOffer->getIdOffer(), $request->request->get('_token'))) {
            foreach ($jobOffer->getApplications() as $application) {
                $entityManager->remove($application);
            }

            $entityManager->remove($jobOffer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
    }

    // JobOfferController.php

    #[Route('/search-cities', name: 'app_job_offer_search_cities', methods: ['GET'])]
    public function searchCities(Request $request, GeonamesService $geonamesService): JsonResponse
    {
        $query = $request->query->get('query');
        $countryCode = $request->query->get('countryCode');
        
        if (empty($query)) {
            return $this->json([]);
        }

        try {
            // If you want to search globally without country code:
            $cities = $geonamesService->fetchCities($countryCode ?? '');
            $filtered = array_filter($cities, fn($city) => stripos($city, $query) !== false);
            return $this->json(array_values($filtered));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
    

#[Route('/get-city-coordinates', name: 'app_job_offer_city_coordinates', methods: ['GET'])]
public function getCityCoordinates(Request $request, GeonamesService $geonamesService): JsonResponse
{
    $city = $request->query->get('city');
    $countryCode = $request->query->get('countryCode');
    
    if (empty($city)) {
        return $this->json(['error' => 'City is required'], 400);
    }

    try {
        $coordinates = $geonamesService->fetchCityCoordinates($city, $countryCode);
        return $this->json($coordinates ?: ['error' => 'City not found']);
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], 400);
    }
}

    
}
