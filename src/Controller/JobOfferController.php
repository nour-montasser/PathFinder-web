<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\JobOfferRepository;

#[Route('/jobOffer')]
final class JobOfferController extends AbstractController
{
    #[Route(name: 'app_job_offer_index', methods: ['GET'])]
    public function index(JobOfferRepository $repository, Request $request): Response
    {
        $searchTerm = trim($request->query->get('search', ''));
        $filters = [
            'types' => $request->query->all('types') ?? [],
            'fields' => $request->query->all('fields') ?? [],
            'education' => $request->query->all('education') ?? []
        ];
        $onlyMyJobs = $request->query->getBoolean('my_jobs', false);
    
        $jobOffers = $repository->findFilteredJobOffers(
            $searchTerm,
            $filters,
            $onlyMyJobs ? $this->getUser() : null
        );
        $recentJobs = $repository->findBy([], ['datePosted' => 'DESC'], 5);
    
        return $this->render('job_offer/index.html.twig', [
            'job_offers' => $jobOffers,
            'search_term' => $searchTerm,
            'recent_jobs' => $recentJobs,
            'selected_types' => $filters['types'],
            'selected_fields' => $filters['fields'],
            'selected_education' => $filters['education'],
            'only_my_jobs' => $onlyMyJobs
        ]);
    }

    #[Route('/new', name: 'app_job_offer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jobOffer = new JobOffer();
        $jobOffer->setDatePosted(new \DateTime("now"));
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
    public function show(JobOffer $jobOffer): Response
    {
        return $this->render('job_offer/show.html.twig', [
            'job_offer' => $jobOffer,
        ]);
    }

    #[Route('/{id_offer}/edit', name: 'app_job_offer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, JobOffer $jobOffer, EntityManagerInterface $entityManager): Response
    {
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
public function delete(Request $request, JobOffer $jobOffer, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$jobOffer->getIdOffer(), $request->request->get('_token'))) {
        // First delete all related applications
        foreach ($jobOffer->getApplications() as $application) {
            $entityManager->remove($application);
        }
        
        // Then delete the job offer
        $entityManager->remove($jobOffer);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_job_offer_index', [], Response::HTTP_SEE_OTHER);
}
}
