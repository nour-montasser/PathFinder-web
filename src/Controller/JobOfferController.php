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

    $onlyMyJobs = $request->query->has('my_jobs') 
        ? $request->query->getBoolean('my_jobs')
        : false;

    $jobOffers = $repository->findFilteredJobOffers(
        $searchTerm,
        $filters,
        $onlyMyJobs ? $user : null
    );

    // Initialize hasApplied array
    $hasApplied = [];
    if ($user->getRole() === 2) {
        foreach ($jobOffers as $jobOffer) {
            $hasApplied[$jobOffer->getIdOffer()] = $applicationJobRepository->hasUserAppliedToJob(
                $user->getId_user(),
                $jobOffer->getIdOffer()
            );
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
        $onlyMyJobs ? ['user' => $user] : [],
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
        'only_my_jobs' => $onlyMyJobs,
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
    public function show(Job_offer $jobOffer): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();



        return $this->render('job_offer/show.html.twig', [
            'job_offer' => $jobOffer,
        ]);
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
