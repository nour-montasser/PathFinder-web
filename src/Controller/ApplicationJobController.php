<?php

namespace App\Controller;

use App\Entity\ApplicationJob;
use App\Form\ApplicationJobType;
use App\Repository\ApplicationJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Base;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Cv;
use App\Repository\CvRepository;
use App\Repository\JobOfferRepository;
use App\Entity\Job_offer;
use App\Entity\App_user;


#[Route('/application/job')]
final class ApplicationJobController extends BaseController
{
    #[Route('/', name: 'app_application_job_index', methods: ['GET'])]
    public function index(Request $request, ApplicationJobRepository $applicationJobRepository): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
    
        $application_jobs = $applicationJobRepository->findFilteredApplications(
            $user,
            $request->query->get('search'),
            $request->query->all('status') ?: null,
            $request->query->get('sort', 'date')
        );
    
        if ($request->query->get('ajax')) {
            return $this->render('application_job/index.html.twig', [
                'application_jobs' => $application_jobs,
                'is_ajax' => true
            ]);
        }
    
        return $this->render('application_job/index.html.twig', [
            'application_jobs' => $application_jobs,
            'is_ajax' => false
        ]);
    }

    #[Route('/new', name: 'app_application_job_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $this->ensureUserSession();
    $user = $this->getCurrentUser();
    
    $jobOfferId = $request->query->get('job_offer_id');
    $jobOffer = $entityManager->getRepository(Job_offer::class)->find($jobOfferId);
    
    $userCvs = $entityManager->getRepository(Cv::class)->findBy(['user' => $user]);
    

    $application = $entityManager->getRepository(ApplicationJob::class)->findOneBy([
        'user' => $user,
        'jobOffer' => $jobOffer
    ]);
    
    if ($application && $application->getStatus() === 'Pending') {
        $this->addFlash('warning', 'You have already applied to this job offer.');
        return $this->redirectToRoute('app_application_job_show', [
            'application_id' => $application->getApplication_id()
        ]);
    }
    
     // Only create new if no existing application
    if (!$application) {
        $application = new ApplicationJob();
        $application->setUser($user)
                   ->setJobOffer($jobOffer)
                   ->setStatus('Applying-1')
                   ->setDateApplication(new \DateTime());
        
        $entityManager->persist($application);
        // Don't flush here - wait until we have some data
    }
    
    $form = $this->createForm(ApplicationJobType::class, $application, [
        'available_cvs' => $userCvs
    ]);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $requestedStep = (int) $request->request->get('current_step', 1);
        
        // Update status based on current step
        $application->setStatus($requestedStep === 4 ? 'Pending' : 'Applying-' . $requestedStep);
        
        // Final submission
        if ($requestedStep === 4) {
            $this->addFlash('success', 'Application submitted successfully!');
        $application->setDateApplication(new \DateTime());
        $entityManager->persist($application);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_job_offer_show', [
                'id_offer' => $jobOffer->getIdOffer()
            ]);
        }
        
        $entityManager->flush();
        
        // Redirect back to the form to continue
        return $this->redirectToRoute('app_application_job_new', [
            'job_offer_id' => $jobOfferId
        ]);
    }
    
    $currentStep = (int) str_replace('Applying-', '', $application->getStatus());
    
    return $this->render('application_job/new.html.twig', [
        'form' => $form->createView(),
        'job_offer' => $jobOffer,
        'current_step' => $currentStep
    ]);
}
    

    #[Route('/{application_id}/edit', name: 'app_application_job_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ApplicationJob $applicationJob, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApplicationJobType::class, $applicationJob);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_application_job_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application_job/edit.html.twig', [
            'application_job' => $applicationJob,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{application_id}', name: 'app_application_job_delete', methods: ['POST'])]
public function delete(Request $request, ApplicationJob $applicationJob, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$applicationJob->getApplication_id(), $request->request->get('_token'))) {
        $entityManager->remove($applicationJob);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_application_job_index');
}



    #[Route('/show/{application_id}', name: 'app_application_job_show', methods: ['GET'])]
    public function show(ApplicationJob $application_job): Response
    {
        $this->ensureUserSession();
        return $this->render('application_job/index.html.twig', [
            'application_jobs' => [$application_job], // Pass as array for consistency
            'show_single_application' => true,
            'application_job' => $application_job
        ]);
    }


}