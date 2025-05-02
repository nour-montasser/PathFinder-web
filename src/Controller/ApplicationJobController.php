<?php

namespace App\Controller;

use App\Entity\ApplicationJob;
use App\Form\ApplicationJobType;
use App\Repository\ApplicationJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Base;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormInterface;
use App\Form\ScheduleInterviewType;
use App\Service\GoogleCalendarService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Entity\Cv;
use App\Repository\CvRepository;
use App\Repository\JobOfferRepository;
use App\Entity\Job_offer;
use App\Entity\App_user;
use App\Entity\Coverletter;
use Knp\Snappy\Pdf;
use Dompdf\Dompdf;
use App\Service\PdfGenerator;
use App\Service\ApplicationMailer;
use App\Service\AiCoverLetterGenerator;
use App\Service\CsvExporter;




#[Route('/application/job')]
final class ApplicationJobController extends BaseController
{
    #[Route('/', name: 'app_application_job_index', methods: ['GET'])]
public function index(Request $request, ApplicationJobRepository $applicationJobRepository, PaginatorInterface $paginator): Response
{
    $this->ensureUserSession();
    $user = $this->getCurrentUser();

    $query = $applicationJobRepository->findFilteredApplicationsQuery(
        $user,
        $request->query->get('search'),
        $request->query->all('status') ?: null,
        $request->query->get('sort', 'date')
    );

    // For AJAX requests, return all results without pagination
    if ($request->query->get('ajax')) {
        $applications = $query->getResult();
        return $this->render('application_job/index.html.twig', [
            'application_jobs' => $applications,
            'is_ajax' => true,
            'show_pagination' => false
        ]);
    }

    // For normal requests, use pagination
    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        6 // items per page
    );

    return $this->render('application_job/index.html.twig', [
        'application_jobs' => $pagination,
        'is_ajax' => false,
        'show_pagination' => true
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
    
        if ($application && $application->getStatus() === 'Pending') {
            $this->addFlash('warning', 'You have already submitted this application');
            return $this->redirectToRoute('app_application_job_index');
        }
    
        if (!$application) {
            $application = (new ApplicationJob())
                ->setUser($user)
                ->setJobOffer($jobOffer)
                ->setStatus('Applying-1')
                ->setDateApplication(new \DateTime());
            $entityManager->persist($application);
        }
    
        $currentStep = (int) str_replace('Applying-', '', $application->getStatus());
        $userCvs = $entityManager->getRepository(Cv::class)->findBy(['user' => $user]);
    
        $form = $this->createForm(ApplicationJobType::class, $application, [
            'available_cvs' => $userCvs,
            'current_step' => $currentStep
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            $action = $request->request->get('action');
            
            // Determine next step
            if ($action === 'prev') {
                $nextStep = max($currentStep - 1, 1);
            } else {
                // Only validate when moving forward
                if (!$form->isValid()) {
                    return $this->render('application_job/new.html.twig', [
                        'form' => $form->createView(),
                        'job_offer' => $jobOffer,
                        'current_step' => $currentStep,
                        'cover_letter' => [
                            'subject' => $application->getCoverletter()?->getSubject() ?? '',
                            'content' => $application->getCoverletter()?->getContent() ?? ''
                        ]
                    ]);
                }
                
                $nextStep = min($currentStep + 1, 4);
            }
    
            // Process cover letter data when moving forward from step 2
            if ($currentStep === 2 && $action === 'next') {
                $coverLetterForm = $form->get('coverletter');
                $subject = $coverLetterForm->get('subject')->getData();
                $content = $coverLetterForm->get('content')->getData();
    
                if (!$application->getCoverletter()) {
                    $coverLetter = new Coverletter();
                    $coverLetter->setApplication($application);
                    $application->setCoverletter($coverLetter);
                    $entityManager->persist($coverLetter);
                }
    
                $application->getCoverletter()
                    ->setSubject($subject)
                    ->setContent($content);
            }
    
            // Final submission
            if ($nextStep === 4 && $request->request->get('submit_final')) {
                $application->setStatus('Pending');
                $application->setDateApplication(new \DateTime());
                $entityManager->flush();
                $this->addFlash('success', 'Application submitted successfully!');
                return $this->redirectToRoute('app_job_offer_show', [
                    'id_offer' => $jobOffer->getIdOffer()
                ]);
            }
    
            $application->setStatus('Applying-' . $nextStep);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_application_job_new', [
                'job_offer_id' => $jobOfferId
            ]);
        }
    
        return $this->render('application_job/new.html.twig', [
            'form' => $form->createView(),
            'job_offer' => $jobOffer,
            'current_step' => $currentStep,
            'cover_letter' => [
                'subject' => $application->getCoverletter()?->getSubject() ?? '',
                'content' => $application->getCoverletter()?->getContent() ?? ''
            ]
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

    $currentStep = (int) str_replace('Applying-', '', $application->getStatus());

    $form = $this->createForm(ApplicationJobType::class, $application, [
        'available_cvs' => $userCvs,
        'current_step' => $currentStep

    ]);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $requestedStep = (int) $request->request->get('current_step', 1);
        $currentStep = (int) str_replace('Applying-', '', $application->getStatus());
        $action = $request->request->get('action', 'next');
        if ($currentStep === 1 && $action === 'next') {
            // Create cover letter if not exists
            if (!$application->getCoverletter()) {
                $coverLetter = new Coverletter();
                $coverLetter->setSubject('');
                $coverLetter->setContent('');
                
                // Set the association properly
                $coverLetter->setApplication($application); // Set this first
                $application->setCoverletter($coverLetter); // Then set the reverse
                
                $entityManager->persist($coverLetter);
            }
        }
        
        // Only process cover letter data when on step 2
        if ($currentStep === 2) {
            $coverLetterData = $form->get('coverletter')->getData();
            if ($coverLetterData) {
                $subject = $form->get('coverletter')->get('subject')->getData() ?: '';
                $content = $form->get('coverletter')->get('content')->getData() ?: '';
                
                $coverLetter = $application->getCoverletter();
                $coverLetter->setSubject($subject);
                $coverLetter->setContent($content);
            }
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


#[Route('/application/{id}/accept', name: 'app_application_job_accept')]
public function accept(
    ApplicationJob $application,
    Request $request,
    ApplicationMailer $mailer,
    GoogleCalendarService $calendarService,
    EntityManagerInterface $entityManager
): Response {
    $tokenPath = $this->getParameter('kernel.project_dir').'/config/google_token.json';
    if (!file_exists($tokenPath)) {
        $request->getSession()->set('google_auth_redirect', $request->getUri());
        return $this->redirectToRoute('app_google_auth');
    }

    $form = $this->createForm(ScheduleInterviewType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Handle AJAX requests
        if ($request->isXmlHttpRequest()) {
            try {
                if (!$form->isValid()) {
                    return $this->json([
                        'success' => false,
                        'errors' => $this->getFormErrors($form),
                    ], 400);
                }

                $data = $form->getData();
                $startTime = null;
                $meetLink = null;
                
                if (!$data['scheduleNow'] && $data['interviewDate'] && $data['interviewTime']) {
                    $startTime = \DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $data['interviewDate']->format('Y-m-d') . ' ' . $data['interviewTime']->format('H:i:s')
                    );
                }

                if ($this->getParameter('kernel.environment') !== 'test') {
                    $result = $calendarService->scheduleInterview(
                        "nourmo49@gmail.com",
                        $application->getUser()->getEmail(),
                        $application->getJobOffer()->getTitle(),
                        $startTime
                    );

                    if (!$result['success']) {
                        throw new \RuntimeException($result['error']);
                    }
                    
                    $meetLink = $result['meetLink'];
                } else {
                    $meetLink = 'https://meet.google.com/mock-interview-link';
                }

                $application->setStatus('Accepted');
                $application->getJobOffer()->setNumberOfSpots(
                    $application->getJobOffer()->getNumberOfSpots() - 1
                );
                $entityManager->flush();

                $mailer->sendApplicationStatusEmail(
                    $application, 
                    'Accepted',
                    $meetLink,
                    $startTime
                );

                return $this->json([
                    'success' => true,
                    'meetLink' => $meetLink,
                    'message' => 'Interview scheduled successfully!',
                    'redirect' => $this->generateUrl('app_job_offer_show', [
                        'id_offer' => $application->getJobOffer()->getIdOffer()
                    ])
                ]);

            } catch (\Exception $e) {
                return $this->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
        }

        // Traditional form submission
        if ($form->isValid()) {
            $data = $form->getData();
            $startTime = null;
            $meetLink = null;
            
            if (!$data['scheduleNow'] && $data['interviewDate'] && $data['interviewTime']) {
                $startTime = \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $data['interviewDate']->format('Y-m-d') . ' ' . $data['interviewTime']->format('H:i:s')
                );
            }

            if ($this->getParameter('kernel.environment') !== 'test') {
                $result = $calendarService->scheduleInterview(
                    "nourmo49@gmail.com",
                    $application->getUser()->getEmail(),
                    $application->getJobOffer()->getTitle(),
                    $startTime
                );

                if (!$result['success']) {
                    $this->addFlash('error', 'Failed to schedule interview: ' . $result['error']);
                    return $this->redirectToRoute('app_job_offer_show', ['id_offer' => $application->getJobOffer()->getIdOffer()]);
                }
                
                $meetLink = $result['meetLink'];
            } else {
                $meetLink = 'https://meet.google.com/mock-interview-link';
            }

            $application->setStatus('Accepted');
            $entityManager->flush();

            $mailer->sendApplicationStatusEmail(
                $application, 
                'Accepted',
                $meetLink,
                $startTime
            );

            $this->addFlash('success', 'Interview scheduled successfully!');
            $this->addFlash('meet_link', $meetLink);
            return $this->redirectToRoute('app_job_offer_show', ['id_offer' => $application->getJobOffer()->getIdOffer()]);
        }
    }

    return $this->render('application_job/schedule_interview.html.twig', [
        'form' => $form->createView(),
        'application' => $application,
    ]);
}

private function getFormErrors(FormInterface $form): array
{
    $errors = [];
    foreach ($form->getErrors(true) as $error) {
        $errors[$error->getOrigin()->getName()] = $error->getMessage();
    }
    return $errors;
}


#[Route('/application/google/auth', name: 'app_google_auth')]
public function googleAuth(Request $request, GoogleCalendarService $calendarService): Response
{
    // Store the original URL in session before redirecting to Google
    $request->getSession()->set('google_auth_redirect', $request->headers->get('referer'));
    
    $authUrl = $calendarService->getAuthUrl();
    return $this->redirect($authUrl);
}

#[Route('/google/auth/callback', name: 'app_google_auth_callback')]
public function googleAuthCallback(Request $request, GoogleCalendarService $calendarService): Response
{
    $code = $request->query->get('code');
    if (!$code) {
        throw new \RuntimeException('No authorization code provided.');
    }

    $calendarService->handleAuthCallback($code);
    
    // Redirect back to the original URL stored in session
    $redirectUrl = $request->getSession()->get('google_auth_redirect', $this->generateUrl('app_job_offer_index'));
    return $this->redirect($redirectUrl);
}

#[Route('/{id}/reject', name: 'app_application_job_reject', methods: ['POST'])]
public function reject(
    ApplicationJob $applicationJob, 
    EntityManagerInterface $entityManager,
    ApplicationMailer $mailer
): Response {
    $applicationJob->setStatus('Rejected');
    $entityManager->flush();
    
    // Send rejection email
    $mailer->sendApplicationStatusEmail($applicationJob, 'Rejected');
    
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



#[Route('/dashboard/export-excel', name: 'dashboard_export_excel', methods: ['GET'])]
public function exportExcel(
    JobOfferRepository $jobOfferRepository,
    ApplicationJobRepository $applicationJobRepository,
    CsvExporter $csvExporter
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

    $chartData = [
        'application_status' => [
            'labels' => array_keys($stats['application_statuses']),
            'data' => array_values($stats['application_statuses']),
            'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b','#6f42c1']
        ],
        'job_applications' => $this->prepareJobApplicationsChartData($jobOffers),
    ];

    
    return $csvExporter->exportDashboardData([
        'stats' => $stats,
        'job_offers' => $jobOffers,
        'chart_data' => $chartData,
        'current_user' => $user,


    ]);
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

    $chartData = [
        'application_status' => [
            'labels' => array_keys($stats['application_statuses']),
            'data' => array_values($stats['application_statuses']),
            'colors' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b','#6f42c1']
        ],
        'job_applications' => $this->prepareJobApplicationsChartData($jobOffers),
    ];

    // Use the PDF generator service to create and return the response
    return $pdfGenerator->generateDashboardPdf([
        'stats' => $stats,
        'chart_data' => $chartData,
        'job_offers' => $jobOffers,
        'current_user' => $user,
    ], $request);
}

// src/Controller/GoogleAuthTestController.php
#[Route('/google/test', name: 'google_auth_test')]  
public function testAuth(GoogleCalendarService $calendarService): Response
{   
    $authUrl = $calendarService->getClient()->createAuthUrl();
    return $this->redirect($authUrl);
}




#[Route('/application/generate-coverletter/{job_offer_id}', name: 'app_application_generate_coverletter', methods: ['POST'])]
public function generateCoverLetter(
    Request $request,
    EntityManagerInterface $entityManager,
    int $job_offer_id,
    AiCoverLetterGenerator $aiGenerator
): Response {
    $this->ensureUserSession();
    $user = $this->getCurrentUser();
    
    $jobOffer = $entityManager->getRepository(Job_offer::class)->find($job_offer_id);
    if (!$jobOffer) {
        return $this->json(['error' => 'Job offer not found'], Response::HTTP_NOT_FOUND);
    }
    
    // Get user's CV data
    $userCvs = $entityManager->getRepository(Cv::class)->findBy(['user' => $user]);
    if (empty($userCvs)) {
        return $this->json(['error' => 'No CV found for this user'], Response::HTTP_BAD_REQUEST);
    }
    
    try {
        // Use the first CV (or let user select one in a more advanced version)
        $cv = $userCvs[0];
        
        $result = $aiGenerator->generateCoverLetter($cv, $jobOffer);
        
        return $this->json([
            'subject' => $result['subject'],
            'content' => $result['content']
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'error' => $e->getMessage()
        ], $e instanceof HttpException ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}


