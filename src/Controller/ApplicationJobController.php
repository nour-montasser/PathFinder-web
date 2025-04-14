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
use App\Entity\Coverletter;
use Knp\Snappy\Pdf;
use Dompdf\Dompdf;
use App\Service\PdfGenerator;


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
    
    if (!$jobOffer) {
        throw $this->createNotFoundException('Job offer not found');
    }

    // Find or create application
    $application = $entityManager->getRepository(ApplicationJob::class)->findOneBy([
        'user' => $user,
        'jobOffer' => $jobOffer
    ]);

    // Handle existing applications
    if ($application) {
        if ($application->getStatus() === 'Pending') {
            $this->addFlash('warning', 'You have already submitted this application');
            return $this->redirectToRoute('app_application_job_index');
        }
    } else {
        // Create new application
        $application = (new ApplicationJob())
            ->setUser($user)
            ->setJobOffer($jobOffer)
            ->setStatus('Applying-1')  // Start at step 1
            ->setDateApplication(new \DateTime());
        $entityManager->persist($application);
        
    }

    $userCvs = $entityManager->getRepository(Cv::class)->findBy(['user' => $user]);
    $form = $this->createForm(ApplicationJobType::class, $application, [
        'available_cvs' => $userCvs
    ]);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $requestedStep = (int) $request->request->get('current_step', 1);
        $currentStep = (int) str_replace('Applying-', '', $application->getStatus());

        // Only process cover letter if we're actually on step 2
        // In the new action, modify the step 2 validation part:
if ($currentStep === 2) {
    $subject = $request->request->get('subject', '');
    $content = $request->request->get('content', '');
    
    // Validate required fields
    $hasErrors = false;
    
    // In your new action, after checking for empty fields:
if (empty($subject)) {
    $this->addFlash('error', 'subject'); // Just flag which field has error
    $hasErrors = true;
}

if (empty($content)) {
    $this->addFlash('error', 'content'); // Just flag which field has error
    $hasErrors = true;
}
    
    if ($hasErrors) {
        return $this->redirectToRoute('app_application_job_new', [
            'job_offer_id' => $jobOfferId
        ]);
    }
    
    $coverLetter = $application->getCoverletter();
    if (!$coverLetter) {
        $coverLetter = new Coverletter($application);
    }
    
    $coverLetter->setSubject($subject);
    $coverLetter->setContent($content);
    
    $application->setCoverletter($coverLetter);
    $entityManager->persist($coverLetter);
}

        // Determine next step based on button click
        $action = $request->request->get('action', 'next');
        if ($action === 'next') {
            $nextStep = min($currentStep + 1, 5); // Don't go beyond step 4
        } else {
            $nextStep = max($currentStep - 1, 1); // Don't go below step 1
        }

        // Update status based on next step
        $application->setStatus($nextStep === 5 ? 'Pending' : 'Applying-' . $nextStep);
        
        // Final submission
        if ($nextStep === 4 && $request->request->get('submit_final')) {
            $application->setDateApplication(new \DateTime());
            $this->addFlash('success', 'Application submitted successfully!');
            $entityManager->flush();
            
            return $this->redirectToRoute('app_job_offer_show', [
                'id_offer' => $jobOffer->getIdOffer()
            ]);
        }

        $entityManager->flush();
        
        return $this->redirectToRoute('app_application_job_new', [
            'job_offer_id' => $jobOfferId
        ]);
    }
    
    $currentStep = (int) str_replace('Applying-', '', $application->getStatus());
    
    // Pre-fill cover letter fields if they exist
    $coverLetterData = [
        'subject' => '',
        'content' => ''
    ];
    
    if ($application->getCoverletter()) {
        $coverLetterData = [
            'subject' => $application->getCoverletter()->getSubject(),
            'content' => $application->getCoverletter()->getContent()
        ];
    }
    
    return $this->render('application_job/new.html.twig', [
        'form' => $form->createView(),
        'job_offer' => $jobOffer,
        'current_step' => $currentStep,
        'cover_letter' => $coverLetterData
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



#[Route('/{application_id}/edit', name: 'app_application_job_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, ApplicationJob $application, EntityManagerInterface $entityManager): Response
{
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    // Make sure only the owner can edit
    if ($application->getUser() !== $user) {
        throw $this->createAccessDeniedException("You cannot edit this application.");
    }

    // If status is "Pending", force Step 4
    if ($application->getStatus() === 'Pending') {
        $application->setStatus('Applying-4');
        $entityManager->flush();
    }

    $jobOffer = $application->getJobOffer();
    $userCvs = $entityManager->getRepository(Cv::class)->findBy(['user' => $user]);

    $form = $this->createForm(ApplicationJobType::class, $application, [
        'available_cvs' => $userCvs
    ]);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $requestedStep = (int) $request->request->get('current_step', 1);
        $currentStep = (int) str_replace('Applying-', '', $application->getStatus());

        // Process cover letter if on step 2
        if ($currentStep === 2) {
            $subject = $request->request->get('subject', '');
            $content = $request->request->get('content', '');
            
            $hasErrors = false;
            
            if (empty($subject)) {
                $this->addFlash('error', 'subject');
                $hasErrors = true;
            }

            if (empty($content)) {
                $this->addFlash('error', 'content');
                $hasErrors = true;
            }
            
            if ($hasErrors) {
                return $this->redirectToRoute('app_application_job_edit', [
                    'application_id' => $application->getApplication_id()
                ]);
            }
            
            $coverLetter = $application->getCoverletter();
            if (!$coverLetter) {
                $coverLetter = new Coverletter($application);
            }
            
            $coverLetter->setSubject($subject);
            $coverLetter->setContent($content);
            
            $application->setCoverletter($coverLetter);
            $entityManager->persist($coverLetter);
        }

        // Handle final submission
        if ($currentStep === 4 && $request->request->get('submit_final')) {
            $application->setStatus('Pending');
            $application->setDateApplication(new \DateTime());
            $entityManager->flush();
            
            $this->addFlash('success', 'Application submitted successfully!');
            return $this->redirectToRoute('app_application_job_index');
        }

        // Normal step navigation
        $action = $request->request->get('action', 'next');
        if ($action === 'next') {
            $nextStep = min($currentStep + 1, 4); // Max step is 4
        } else {
            $nextStep = max($currentStep - 1, 1); // Min step is 1
        }

        $application->setStatus('Applying-' . $nextStep);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_application_job_edit', [
            'application_id' => $application->getApplication_id()
        ]);
    }
    
    $currentStep = (int) str_replace('Applying-', '', $application->getStatus());
    
    // Pre-fill cover letter fields if they exist
    $coverLetterData = [
        'subject' => '',
        'content' => ''
    ];
    
    if ($application->getCoverletter()) {
        $coverLetterData = [
            'subject' => $application->getCoverletter()->getSubject(),
            'content' => $application->getCoverletter()->getContent()
        ];
    }
    
    return $this->render('application_job/new.html.twig', [
        'form' => $form->createView(),
        'job_offer' => $jobOffer,
        'current_step' => $currentStep,
        'application' => $application,
        'cover_letter' => $coverLetterData,
        'is_editing' => true
    ]);
}   


#[Route('/{id}/accept', name: 'app_application_job_accept', methods: ['POST'])]
public function accept(
    ApplicationJob $applicationJob, 
    EntityManagerInterface $entityManager
): Response {
    $applicationJob->setStatus('Accepted');
    $entityManager->flush();
    
    return $this->redirectToRoute('app_job_offer_show', [
        'id_offer' => $applicationJob->getJobOffer()->getIdOffer()
    ]);
}

#[Route('/{id}/reject', name: 'app_application_job_reject', methods: ['POST'])]
public function reject(
    ApplicationJob $applicationJob, 
    EntityManagerInterface $entityManager
): Response {
    $applicationJob->setStatus('Rejected');
    $entityManager->flush();
    
    return $this->redirectToRoute('app_job_offer_show', [
        'id_offer' => $applicationJob->getJobOffer()->getIdOffer()
    ]);
}


#[Route('/dashboard', name: 'app_job_offer_dashboard', methods: ['GET'])]
public function dashboard(
    JobOfferRepository $jobOfferRepository,
    ApplicationJobRepository $applicationJobRepository
): Response {
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    // Get all job offers for this company
    $jobOffers = $jobOfferRepository->findBy(['user' => $user], ['date_posted' => 'DESC']);

    // Get statistics
    $totalApplications = $applicationJobRepository->countApplicationsForCompany($user->getId_user());
    $totalJobs = count($jobOffers);
    
    $previousPeriodJobs = $jobOfferRepository->countJobsFromPreviousPeriod();
    $currentPeriodJobs = $jobOfferRepository->countJobsFromCurrentPeriod();
    $jobsChangePercentage = $previousPeriodJobs > 0 
        ? (($currentPeriodJobs - $previousPeriodJobs) / $previousPeriodJobs) * 100 
        : 0;

    $conversionRate = $applicationJobRepository->calculateConversionRate();
    $popularJobs = $jobOfferRepository->findMostPopularJobs($user->getId_user(), 5);
    $recentApplications = $applicationJobRepository->findRecentApplicationsForCompany($user->getId_user(), 5);
    $applicationStatuses = $applicationJobRepository->getApplicationStatusStats($user->getId_user());
    // In your controller:
$weeklyTrends = $applicationJobRepository->getWeeklyApplicationTrends($user->getId_user(), 8); // Last 8 weeks

    $stats = [
        'total_jobs' => $totalJobs,
        'active_jobs' => $jobOfferRepository->count(['user' => $user]),
        'total_applications' => $totalApplications,
        'application_statuses' => $applicationStatuses,
        'popular_jobs' => $popularJobs,
        'recent_applications' => $recentApplications,
        'jobs_change' => $jobsChangePercentage,
        'avg_applications_per_job' => $totalApplications / max(1, $totalJobs),
        'conversion_rate' => $conversionRate,
        'popular_job' => $popularJobs[0] ?? null,
        'conversion_change' => $applicationJobRepository->calculateConversionRateChange(),

    ];

    $trends = $applicationJobRepository->getApplicationTrends(
        $this->getCurrentUser()->getId_user(),
        4 // Get last 4 weeks of data
    );
    $timelineData = $this->prepareTimelineData($trends);

    // Prepare data for charts
    $chartData = [
        'application_status' => [
            'labels' => array_keys($stats['application_statuses']),
            'data' => array_values($stats['application_statuses']),
            'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b','#6f42c1']
        ],
        'job_applications' => $this->prepareJobApplicationsChartData($jobOffers),
        'timeline' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], // Replace with real data
            'data' => [12, 19, 3, 5, 2, 3] // Replace with real data
        ]
    ];
 

    return $this->render('application_job/dashboard.html.twig', [
        'stats' => $stats,
        'chart_data' => $chartData,
        'job_offers' => $jobOffers,
        'timeline_data' => $timelineData,

    ]);
}

private function prepareTimelineData(array $trends): array
{
    $statusCounts = [];
    $colors = [
        'Applying-2' => '#6f42c1',
        'Applying-3' => '#4e73df',
        'Applying-4' => '#1cc88a',
        'Pending' => '#f6c23e',
        'Accepted' => '#36b9cc',
        'Rejected' => '#e74a3b'
    ];

    foreach ($trends as $trend) {
        $date = new \DateTime($trend['date']);
        $weekNumber = $date->format('o-W'); // ISO year and week number
        $dayName = $trend['day_name'];
        
        if (!isset($statusCounts[$weekNumber][$dayName][$trend['status']])) {
            $statusCounts[$weekNumber][$dayName][$trend['status']] = 0;
        }
        $statusCounts[$weekNumber][$dayName][$trend['status']] += $trend['count'];
    }

    return [
        'colors' => $colors,
        'weeks' => $statusCounts
    ];
}

private function prepareJobApplicationsChartData(array $jobOffers): array
{
    $data = [
        'labels' => [],
        'datasets' => [
            [
                'label' => 'Applications',
                'backgroundColor' => '#4e73df',
                'borderColor' => '#4e73df',
                'data' => []
            ]
        ]
    ];

    foreach ($jobOffers as $job) {
        $data['labels'][] = $job->getTitle();
        $data['datasets'][0]['data'][] = count($job->getApplications());
    }

    return $data;
}











#[Route('/dashboard/export-pdf', name: 'dashboard_export_pdf', methods: ['GET'])]
public function exportPdf(
    JobOfferRepository $jobOfferRepository,
    ApplicationJobRepository $applicationJobRepository,
    PdfGenerator $pdfGenerator,
    Request $request
): Response {
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    // Get all job offers for this company
    $jobOffers = $jobOfferRepository->findBy(['user' => $user], ['date_posted' => 'DESC']);

    // Get statistics
    $totalApplications = $applicationJobRepository->countApplicationsForCompany($user->getId_user());
    $totalJobs = count($jobOffers);
    
    $previousPeriodJobs = $jobOfferRepository->countJobsFromPreviousPeriod();
    $currentPeriodJobs = $jobOfferRepository->countJobsFromCurrentPeriod();
    $jobsChangePercentage = $previousPeriodJobs > 0 
        ? (($currentPeriodJobs - $previousPeriodJobs) / $previousPeriodJobs) * 100 
        : 0;

    $conversionRate = $applicationJobRepository->calculateConversionRate();
    $popularJobs = $jobOfferRepository->findMostPopularJobs($user->getId_user(), 5);
    $recentApplications = $applicationJobRepository->findRecentApplicationsForCompany($user->getId_user(), 5);
    $applicationStatuses = $applicationJobRepository->getApplicationStatusStats($user->getId_user());
    $weeklyTrends = $applicationJobRepository->getWeeklyApplicationTrends($user->getId_user(), 8);

    $stats = [
        'total_jobs' => $totalJobs,
        'active_jobs' => $jobOfferRepository->count(['user' => $user]),
        'total_applications' => $totalApplications,
        'application_statuses' => $applicationStatuses,
        'popular_jobs' => $popularJobs,
        'recent_applications' => $recentApplications,
        'jobs_change' => $jobsChangePercentage,
        'avg_applications_per_job' => $totalApplications / max(1, $totalJobs),
        'conversion_rate' => $conversionRate,
        'popular_job' => $popularJobs[0] ?? null,
        'conversion_change' => $applicationJobRepository->calculateConversionRateChange(),
    ];

    $trends = $applicationJobRepository->getApplicationTrends($user->getId_user(), 4);
    $timelineData = $this->prepareTimelineData($trends);

    $chartData = [
        'application_status' => [
            'labels' => array_keys($stats['application_statuses']),
            'data' => array_values($stats['application_statuses']),
            'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b','#6f42c1']
        ],
        'job_applications' => $this->prepareJobApplicationsChartData($jobOffers),
    ];

    return $pdfGenerator->generateDashboardPdf([
        'stats' => $stats,
        'chart_data' => $chartData,
        'job_offers' => $jobOffers,
        'timeline_data' => $timelineData,
        'current_user' => $user, // Add this line
    ], $request);
}





}