<?php

namespace App\Controller;

use App\Entity\Job_offer;
use App\Form\JobOfferType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\JobOfferRepository;

#[Route('/jobOffer')]
final class JobOfferController extends BaseController
{
    #[Route(name: 'app_job_offer_index', methods: ['GET'])]
    public function index(JobOfferRepository $repository, Request $request): Response
    {
        // Ensure user is logged in and session is set
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $searchTerm = trim($request->query->get('search', ''));
        $filters = [
            'types' => $request->query->all('types') ?? [],
            'fields' => $request->query->all('fields') ?? [],
            'education' => $request->query->all('education') ?? []
        ];

        // Always show only the current user's job offers
        $jobOffers = $repository->findFilteredJobOffers(
            $searchTerm,
            $filters,
            $user // Pass the current user to filter their jobs
        );

        $recentJobs = $repository->findBy(['user' => $user], ['date_posted' => 'DESC'], 5);

        return $this->render('job_offer/index.html.twig', [
            'job_offers' => $jobOffers,
            'search_term' => $searchTerm,
            'recent_jobs' => $recentJobs,
            'selected_types' => $filters['types'],
            'selected_fields' => $filters['fields'],
            'selected_education' => $filters['education'],
            'only_my_jobs' => true // Always true now
        ]);
    }

    #[Route('/new', name: 'app_job_offer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
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

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($jobOffer);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('job_offer/new.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form,
        ]);
    }

    #[Route('/{id_offer}', name: 'app_job_offer_show', methods: ['GET'])]
    public function show(Job_offer $jobOffer): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
        
        if (!$user || $jobOffer->getUser() !== $user) {
            throw $this->createAccessDeniedException('You can only view your own job offers');
        }

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

        if ($this->isCsrfTokenValid('delete'.$jobOffer->getIdOffer(), $request->request->get('_token'))) {
            foreach ($jobOffer->getApplications() as $application) {
                $entityManager->remove($application);
            }
            
            $entityManager->remove($jobOffer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
    }
}