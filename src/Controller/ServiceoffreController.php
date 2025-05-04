<?php

namespace App\Controller;

use App\Entity\Serviceoffre;
use App\Entity\Applicationservice;
use App\Entity\App_user;
use App\Form\ServiceOffreType;
use App\Form\ApplicationserviceType;
use App\Repository\ServiceoffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use App\Service\AIDescriptionGenerator; // Ensure this is the correct namespace for the class
use App\Repository\ApplicationserviceRepository; // Add the correct namespace for ApplicationserviceRepository
use App\Service\CurrencyConverter; // Ensure this is the correct namespace for the CurrencyConverter class

#[Route('/serviceoffre')]
final class ServiceoffreController extends BaseController
{
    #[Route('/', name: 'app_serviceoffre_index', methods: ['GET', 'POST'])]
public function index(
    Request $request, 
    EntityManagerInterface $entityManager,
    AIDescriptionGenerator $aiGenerator,
    CurrencyConverter $currencyConverter
): Response {
   
    $showOnlyMyJobs = $request->query->getBoolean('my_jobs');

    $this->ensureUserSession();
    $user = $showOnlyMyJobs ? $this->getCurrentUser() :null;
    $showOnlyMyJobs=false;
    

    $serviceoffres = $user
        ? $entityManager->getRepository(Serviceoffre::class)->findBy(['user' => $user])
        : $entityManager->getRepository(Serviceoffre::class)->findAll();

    $serviceoffre = new Serviceoffre();
    $serviceoffre->setDatePosted(new \DateTime());
    $serviceoffre->setDescription(''); // Initialize with empty string to prevent null

    $form = $this->createForm(ServiceOffreType::class, $serviceoffre);
    $form->handleRequest($request);
   
    



    $isFormOpen = false;

    // Handle AI description generation
    if ($request->request->has('generate_description')) {
        $isFormOpen = true;
        $title = $form->get('title')->getData();

        if (empty($title)) {
            $this->addFlash('error', 'Please enter a title to generate description');
        } else {
            try {
                $prompt = "Generate a professional service description for: $title";

                // Add additional context to the prompt if available
                $field = $form->get('field')->getData();
                $experienceLevel = $form->get('experience_level')->getData();

                if ($field) $prompt .= " in field: $field";
                if ($experienceLevel) $prompt .= " for $experienceLevel level";

                // Generate description using AI
                $generatedDescription = $aiGenerator->generateDescription($prompt);

                // If the generated description is null, set a default message
                if ($generatedDescription === null) {
                    $this->addFlash('warning', 'AI generated an empty description');
                    $serviceoffre->setDescription('No description could be generated');
                } else {
                    // Set the generated description in the entity
                    $serviceoffre->setDescription($generatedDescription);  // <-- Add this line
                    $this->addFlash('success', 'Description generated successfully');
                }

                
                
                

            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to generate description: '.$e->getMessage());
                $serviceoffre->setDescription(''); // Reset description on error
               
            
            }
        }
    }
     // 🔸 5. AI Price Estimation Trigger (NEW BUTTON HANDLER)
     if ($request->request->has('generate_price')) {
        $isFormOpen = true;

        $title = $form->get('title')->getData();
        $description = $form->get('description')->getData();
        $field = $form->get('field')->getData();
        $experienceLevel = $form->get('experience_level')->getData();

        if (empty($title) || empty($description) || empty($field) || empty($experienceLevel)) {
            $this->addFlash('error', 'All fields must be filled to generate a price.');
        } else {
            try {
                $price = $aiGenerator->generatePriceEstimation($title, $description, $field, $experienceLevel);
                if ($price !== null) {
                    $serviceoffre->setPriceEstimation($price);
                    $form->get('price_estimation')->setData($price);
                    $this->addFlash('success', 'Price estimation generated: €' . $price);
                } else {
                    $this->addFlash('warning', 'AI could not generate a price.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to generate price: ' . $e->getMessage());
            }
        }
    }


    // Handle form submission
    if (!$request->request->has('generate_price') && !$request->request->has('generate_description')) {
    if ($form->isSubmitted() && $form->isValid()) {
        $startDate = $form->get('startDate')->getData();
        $endDate = $form->get('endDate')->getData();

        // Validate dates
        if ($startDate && $endDate && $startDate >= $endDate) {
            $form->get('endDate')->addError(new FormError("La date de fin doit être postérieure à la date de début."));
            $isFormOpen = true;
        } else {
            // Calculate duration
            $interval = $startDate->diff($endDate);
            $months = ($interval->y * 12) + $interval->m;

            if ($months < 1) {
                $serviceoffre->setDuration('less than 1 month');
            } elseif ($months <= 3) {
                $serviceoffre->setDuration('1 to 3 months');
            } elseif ($months <= 6) {
                $serviceoffre->setDuration('3 to 6 months');
            } else {
                $serviceoffre->setDuration('more than 6 months');
            }

            $serviceoffre->setUser($user);
            try {
                $entityManager->persist($serviceoffre);
                $entityManager->flush();
                $this->addFlash('success', 'Service created successfully');
                return $this->redirectToRoute('app_serviceoffre_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error saving service: '.$e->getMessage());
                $isFormOpen = true;
            }
        }
    } elseif ($form->isSubmitted() && !$form->isValid()) {
        $isFormOpen = true;
        $this->addFlash('error', 'Please correct the errors in the form');
    }
}

    // Get distinct fields and skills for filters
    $fields = $entityManager->getRepository(Serviceoffre::class)
        ->createQueryBuilder('s')
        ->select('DISTINCT s.field')
        ->where('s.field IS NOT NULL')
        ->getQuery()
        ->getSingleColumnResult();

    $skills = $entityManager->getRepository(Serviceoffre::class)
        ->createQueryBuilder('s')
        ->select('DISTINCT s.skills')
        ->where('s.skills IS NOT NULL')
        ->getQuery()
        ->getSingleColumnResult();

    // Count new applications
    $newAppCount = 0;
    $firstServiceWithNewApps = null;

    if ($user) {
        $userServices = $entityManager->getRepository(Serviceoffre::class)->findBy(['user' => $user]);
        foreach ($userServices as $service) {
            foreach ($service->getApplicationservices() as $app) {
                if ($app->getStatus() === 'pending') {
                    $newAppCount++;
                    if (!$firstServiceWithNewApps) {
                        $firstServiceWithNewApps = $service;
                    }
                    break;
                }
            }
        }
    }
    $currencies = [
        'USD' => 'United States Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound Sterling',
        'JPY' => 'Japanese Yen',
        'AUD' => 'Australian Dollar',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'TND' => 'Tunisian Dinar',
        'MAD' => 'Moroccan Dirham',
        'AED' => 'United Arab Emirates Dirham',
        'CNY' => 'Chinese Yuan',
        'INR' => 'Indian Rupee',
        'BRL' => 'Brazilian Real',
        'ZAR' => 'South African Rand',
        'SEK' => 'Swedish Krona',
        'NOK' => 'Norwegian Krone',
    ];

    return $this->render('serviceoffre/index.html.twig', [
        'serviceoffres' => $serviceoffres,
        'form' => $form->createView(),
        'fields' => $fields ?? [],
        'skills' => $skills ?? [],
        'sessionUser' => $user,
        'newAppCount' => $newAppCount,
        'firstServiceWithNewApps' => $firstServiceWithNewApps,
        'isFormOpen' => $isFormOpen,
        'generatedDescription' => $serviceoffre->getDescription(),
        'generatedPrice' => $serviceoffre->getPriceEstimation(),
        'currencies' => $currencies,
        
    ]);
}



    #[Route('/{idService}/edit', name: 'app_serviceoffre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Serviceoffre $serviceoffre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceOffreType::class, $serviceoffre);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
    
            if ($startDate && $endDate) {
                $interval = $startDate->diff($endDate);
                $months = ($interval->y * 12) + $interval->m;
    
                if ($months < 1) {
                    $serviceoffre->setDuration('less than 1 month');
                } elseif ($months <= 3) {
                    $serviceoffre->setDuration('1 to 3 months');
                } elseif ($months <= 6) {
                    $serviceoffre->setDuration('3 to 6 months');
                } else {
                    $serviceoffre->setDuration('more than 6 months');
                }
            }
            if ($startDate >= $endDate) {
                $form->get('endDate')->addError(new FormError("La date de fin doit être postérieure à la date de début."));
            }
        
    
            $entityManager->flush();
            return $this->redirectToRoute('app_serviceoffre_index');
        }

    return $this->render('serviceoffre/edit.html.twig', [
        'form' => $form->createView(),
        'serviceoffre' => $serviceoffre,
    ]);
}


    #[Route('/new', name: 'app_serviceoffre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $serviceoffre = new Serviceoffre();
        $serviceoffre->setDatePosted(new \DateTime());
        $form = $this->createForm(ServiceOffreType::class, $serviceoffre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData(); 
            $endDate = $form->get('endDate')->getData();

            if ($startDate && $endDate) {
                $interval = $startDate->diff($endDate);
                $months = ($interval->y * 12) + $interval->m;

                if ($months < 1) {
                    $serviceoffre->setDuration('less than 1 month');
                } elseif ($months <= 3) {
                    $serviceoffre->setDuration('1 to 3 months');
                } elseif ($months <= 6) {
                    $serviceoffre->setDuration('3 to 6 months');
                } else {
                    $serviceoffre->setDuration('more than 6 months');
                }
            }
            if ($startDate >= $endDate) {
                $form->get('endDate')->addError(new FormError("La date de fin doit être postérieure à la date de début."));
            }

            $this->ensureUserSession();
    $user = $this->getCurrentUser();
            $serviceoffre->setUser($user);

            $entityManager->persist($serviceoffre);
            $entityManager->flush();

            return $this->redirectToRoute('app_serviceoffre_index');
        }

        return $this->render('serviceoffre/new.html.twig', [
            'serviceoffre' => $serviceoffre,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idService}/apply', name: 'app_serviceoffre_apply', methods: ['GET', 'POST'])]
    public function apply(Serviceoffre $serviceoffre, Request $request, EntityManagerInterface $em): Response
    {
        $application = new Applicationservice();
        $application->setService($serviceoffre);
        $application->setStatus('pending');
    
        $form = $this->createForm(ApplicationserviceType::class, $application);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->ensureUserSession();
    $user = $this->getCurrentUser();
            if ($user) {
                $application->setUser($user);
            }
    
            $application->setRating(0); // Default rating
    
            $em->persist($application);
            $em->flush();
    
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
    
            return $this->redirectToRoute('app_serviceoffre_index');
        }
    
        // Re-render the form with errors if invalid
        return $this->render('serviceoffre/_apply_modal.html.twig', [
            'serviceoffre' => $serviceoffre,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{idService}/application/{appId}/accept', name: 'client_accept_application', methods: ['POST'])]
    public function acceptApplication(
        Serviceoffre $serviceoffre,
        int $appId,
        EntityManagerInterface $em
    ): Response {
        $application = $em->getRepository(Applicationservice::class)->find($appId);
    
        if (!$application) {
            throw $this->createNotFoundException('Application not found.');
        }
    
        $application->setStatus('accepted');
        $em->flush();
    
        return $this->redirectToRoute('app_serviceoffre_manage', [
            'idService' => $serviceoffre->getIdService()
        ]);
    }
    

    #[Route('/{idService}/application/{appId}/reject', name: 'client_reject_application', methods: ['POST'])]
    public function rejectApplication(Serviceoffre $serviceoffre, Applicationservice $application, EntityManagerInterface $em): Response
    {
        $application->setStatus('rejected');
        $em->flush();

        return $this->redirectToRoute('app_serviceoffre_manage', ['idService' => $serviceoffre->getIdService()]);
    }

    #[Route('/{idService}', name: 'app_serviceoffre_show', methods: ['GET'])]
    public function show(Serviceoffre $serviceoffre): Response
    {
        return $this->render('serviceoffre/show.html.twig', [
            'serviceoffre' => $serviceoffre,
            'applications' => $serviceoffre->getApplicationservices(),
        ]);
    }

    #[Route('/{idService}', name: 'app_serviceoffre_delete', methods: ['POST'])]
    public function delete(Request $request, Serviceoffre $serviceoffre, EntityManagerInterface $entityManager): Response
    {
        var_dump("test");
        if ($this->isCsrfTokenValid('delete'.$serviceoffre->getIdService(), $request->request->get('_token'))) {
            
            $entityManager->remove($serviceoffre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_serviceoffre_index');
    }

    #[Route('/{idService}/drawer', name: 'app_serviceoffre_drawer', methods: ['GET'])]
    public function drawer(Serviceoffre $serviceoffre): Response
    {
        return $this->render('serviceoffre/_details.html.twig', [
            'serviceoffre' => $serviceoffre,
        ]);
    }

    #[Route('/serviceoffre/filter', name: 'app_serviceoffre_filter', methods: ['GET'])]
public function filter(
    Request $request,
    ServiceoffreRepository $repository,
    EntityManagerInterface $em,
    LoggerInterface $logger
): Response {
    try {
        $searchTerm = $request->query->get('search') ?? '';
        $field = $request->query->get('field') ?? '';
        $skills = $request->query->get('skills') ?? '';
        $durations = $request->query->all('durations') ?? [];
        $prices = $request->query->all('prices') ?? [];
        $sort = $request->query->get('sort') ?? 'newest';

        $serviceoffres = $repository->filterServices($field, $skills, $searchTerm, $durations, $prices, $sort);
        $this->ensureUserSession();
    $user = $this->getCurrentUser();

        return $this->render('serviceoffre/_list.html.twig', [
            'serviceoffres' => $serviceoffres,
            'sessionUser' => $user // ✅ pass this in
        ]);
    } catch (\Exception $e) {
        $logger->error('Filter error: ' . $e->getMessage());
        return new JsonResponse(['error' => $e->getMessage()], 500);
    }
}


    private function getSessionUser(Request $request, EntityManagerInterface $em): ?App_user
    {
        $userId = $request->getSession()->get('mock_user_id');
        if (!$userId) return null;
    
        return $em->getRepository(App_user::class)->find($userId);
    }
    

    #[Route('/{idService}/manage', name: 'app_serviceoffre_manage', methods: ['GET'])]
public function manage(Serviceoffre $serviceoffre, Request $request, EntityManagerInterface $em): Response
{
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    $applications = $serviceoffre->getApplicationservices();
    $hired = array_filter($applications->toArray(), fn($app) => $app->getStatus() === 'accepted');

    return $this->render('serviceoffre/manage.html.twig', [
        'serviceoffre' => $serviceoffre,
        'applications' => $applications,
        'hired' => $hired,
        'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
        'sessionUser' => $user,


    ]);
}

#[Route('/freelance/guide', name: 'app_freelance_guide', methods: ['GET'])]
public function guide(Request $request, EntityManagerInterface $em): Response
{
    $fields = $em->getRepository(Serviceoffre::class)
        ->createQueryBuilder('s')
        ->select('DISTINCT s.field')
        ->getQuery()
        ->getSingleColumnResult();
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
    return $this->render('serviceoffre/guide.html.twig', [
        'fields' => $fields,
        'sessionUser' => $user,
    ]);
}

#[Route('/dashboard/freelancers', name: 'app_serviceoffre_dashboard')]
public function freelancerDashboard(ApplicationserviceRepository $appRepo, EntityManagerInterface $em, Request $request): Response
{
    $this->ensureUserSession();
    $user = $this->getCurrentUser();
    $sessionUserId = $user->getId_user(); // Assuming getId_user() returns the ID of the user

    if (!$sessionUserId) {
        throw $this->createAccessDeniedException('No client logged in.');
    }

    $applications = $appRepo->createQueryBuilder('a')
        ->join('a.service', 's')
        ->join('s.user', 'client') // JOIN the client owning the service
        ->where('client.id_user = :clientId')
        ->andWhere('a.status = :status')
        ->setParameter('clientId', $sessionUserId) // 🛠 set each parameter separately
        ->setParameter('status', 'paid')
        ->getQuery()
        ->getResult();

        $this->ensureUserSession();
        $user = $this->getCurrentUser();

    return $this->render('serviceoffre/dashboard.html.twig', [
        'applications' => $applications,
        'sessionUser' => $user
    ]);
}


#[Route('/generate-description', name: 'app_serviceoffre_generate_description', methods: ['POST'])]
public function generateDescription(
    Request $request,
    AIDescriptionGenerator $aiGenerator
): JsonResponse {
    $serviceTitle = $request->request->get('title');
    $serviceTitle = $request->request->get('duration');
    $serviceTitle = $request->request->get('field');
    $serviceTitle = $request->request->get('experience_level');
    if (empty($serviceTitle)) {
        return $this->json([
            'success' => false,
            'error' => 'Please enter a service title first!'
        ], Response::HTTP_BAD_REQUEST);
    }

    $description = $aiGenerator->generateDescription($serviceTitle);

    if ($description === null) {
        return $this->json([
            'success' => false,
            'error' => 'Unable to generate a professional description.'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return $this->json([
        'success' => true,
        'description' => $description
    ]);
}


#[Route('/pay', name: 'stripe_pay')]
public function pay(): Response
{
    return $this->render('stripe/payment.html.twig', [
        'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY']
    ]);
}

#[Route('/dashboard/rate-freelancer/{id}', name: 'rate_freelancer', methods: ['POST'])]

public function rateFreelancer(Request $request, ApplicationserviceRepository $appRepo, EntityManagerInterface $em, int $id): JsonResponse
{
    $application = $appRepo->find($id);

    if (!$application) {
        return new JsonResponse(['error' => 'Application not found.'], 404);
    }

    $rating = (int) $request->request->get('rating');

    if ($rating < 1 || $rating > 5) {
        return new JsonResponse(['error' => 'Invalid rating!'], 400);
    }

    $application->setRating($rating);
    $application->setStatus('completed');
    $em->flush();

    return new JsonResponse(['success' => 'Freelancer rated successfully!']);
    
}




}
