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

#[Route('/jobOffer')]
final class JobOfferController extends BaseController
{
    #[Route(name: 'app_job_offer_index', methods: ['GET'])]
    public function index(
        JobOfferRepository $repository,
        Request $request,
        ApplicationJobRepository $applicationJobRepository
    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
    
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Authentication required'], 401);
            }
            return $this->redirectToRoute('app_login');
        }
    
        $searchTerm = trim($request->query->get('search', ''));
        $filters = [
            'types' => $request->query->all('types') ?? [],
            'fields' => $request->query->all('fields') ?? [],
            'education' => $request->query->all('education') ?? []
        ];
    
        // Automatically filter by user if role is 1 (employer)
        $filterUser = ($user->getRole() === 1) ? $user : null;
    
        $jobOffers = $repository->findFilteredJobOffers(
            $searchTerm,
            $filters,
            $filterUser
        );
    
        $hasApplied = [];
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
    
        if ($request->isXmlHttpRequest() || $request->query->get('ajax')) {
            return $this->render('job_offer/index.html.twig', [
                'job_offers' => $jobOffers,
                'has_applied' => $hasApplied,
                'current_user' => $user,
                'is_ajax' => true
            ]);
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
            'is_ajax' => false
        ]);
    }

    #[Route('/new', name: 'app_job_offer_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        JobOfferRepository $jobOfferRepository
    ): Response {

        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $jobOffer = new Job_offer();
        $jobOffer->setDatePosted(new \DateTime("now"));
        $jobOffer->setUser($user); // Set the current user as the owner
        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        // Get all stats from the JobOfferRepository
        $stats = $jobOfferRepository->getUserStats($user->getId_user());

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($jobOffer);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('job_offer/new.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form,
            'stats' => $stats
        ]);
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
    public function edit(Request $request, Job_offer $jobOffer, EntityManagerInterface $entityManager): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        if (!$user || $jobOffer->getUser() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own job offers');
        }

        $form = $this->createForm(JobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    
}
