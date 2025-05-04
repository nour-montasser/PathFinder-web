<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ApplicationJob;
use App\Entity\Job_offer;
use App\Repository\JobOfferRepository;
use App\Entity\Channel;
use App\Repository\ChannelRepository;
use App\Entity\Message;
use App\Repository\FeedbackRepository;
use App\Entity\Feedback;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Cv;

use Symfony\Component\HttpFoundation\RequestStack;

use Dompdf\Dompdf;  // Add this line
use Dompdf\Options;  // Add this line
use App\Entity\App_user;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class AdminController extends BaseController
{
    private $slugger;
    
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack,
        SluggerInterface $slugger = null
    ) {
        parent::__construct($entityManager, $requestStack);
        $this->slugger = $slugger;
    }

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        // Count job seekers (role 2)
        $jobSeekerCount = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u)')
            ->from('App\Entity\App_user', 'u')
            ->where('u.role = :role')
            ->setParameter('role', 2)
            ->getQuery()
            ->getSingleScalarResult();

        // Count enterprises (role 1)
        $enterpriseCount = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u)')
            ->from('App\Entity\App_user', 'u')
            ->where('u.role = :role')
            ->setParameter('role', 1)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin/dashboard.html.twig', [
            'user_count' => $jobSeekerCount,
            'enterprise_count' => $enterpriseCount
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        // Get all users
        $users = $this->entityManager->getRepository(App_user::class)->findBy([], ['id_user' => 'DESC']);
        
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/cvs', name: 'admin_cvs')]
    public function cvs(EntityManagerInterface $em): Response
    {
           // Build a query that retrieves CVs and join fetch its associations.
        // Using distinct() avoids duplicate CV rows if there are multiple associations.
        $qb = $em->createQueryBuilder();
        $qb->select('c, l, e, cert, u')
            ->from(Cv::class, 'c')
            ->leftJoin('c.languages', 'l')
            ->leftJoin('c.experiences', 'e')
            ->leftJoin('c.certificates', 'cert')
            ->leftJoin('c.user', 'u')
            ->distinct();
        $cvs = $qb->getQuery()->getResult();

        return $this->render('admin/cvs.html.twig', [
            'cvs' => $cvs,
        ]);
    }

    #[Route('/skill-tests', name: 'admin_skill_tests')]
    public function skillTests(): Response
    {
        return $this->render('admin/skill_tests.html.twig');
    }

    #[Route('/freelance', name: 'admin_freelance')]
    public function freelance(): Response
    {
        return $this->render('admin/freelance.html.twig');
    }

    #[Route('/job-offers', name: 'admin_job_offers')]
    public function jobOffers(JobOfferRepository $jobOfferRepository): Response
    {
        // Get all job offers with their applications
        $jobOffers = $jobOfferRepository->findAllWithApplications();

        return $this->render('admin/job_offers.html.twig', [
            'job_offers' => $jobOffers
        ]);
    }

    #[Route('/chats', name: 'admin_chats')]
    public function chats(): Response
    {
        return $this->redirectToRoute('admin_channels');
    }


    #[Route('/channels', name: 'admin_channels')]
    public function channels(ChannelRepository $channelRepository, EntityManagerInterface $entityManager): Response
    {
        // Get all channels with user info
        $channels = $channelRepository->createQueryBuilder('c')
            ->leftJoin('c.initiator', 'i')
            ->leftJoin('c.receiver', 'r')
            ->addSelect('i', 'r')
            ->orderBy('c.time_created', 'DESC')
            ->getQuery()
            ->getResult();

        // Get message counts and last message for each channel
        $messageRepo = $entityManager->getRepository(Message::class);

        foreach ($channels as $channel) {
            // Get message count
            $channel->messageCount = $messageRepo->count(['channel' => $channel]);

            // Get last message
            $channel->lastMessage = $messageRepo->findOneBy(
                ['channel' => $channel],
                ['time_sent' => 'DESC']
            );
        }

        return $this->render('admin/chats.html.twig', [
            'channels' => $channels
        ]);
    }

    #[Route('/channels/{id}/delete', name: 'admin_channel_delete', methods: ['POST'])]
    public function deleteChannel(Channel $channel, ChannelRepository $channelRepository): Response
    {
        $channelRepository->deleteChannelWithMessages($channel);

        $this->addFlash('success', 'Channel and all messages deleted successfully');
        return $this->redirectToRoute('admin_channels');
    }


    #[Route('/job-offers/{id}/delete', name: 'admin_job_offer_delete', methods: ['POST'])]
    public function deleteJobOffer(Job_offer $jobOffer, EntityManagerInterface $entityManager): Response
    {
        // Delete all applications first
        foreach ($jobOffer->getApplications() as $application) {
            $entityManager->remove($application);
        }

        $entityManager->remove($jobOffer);
        $entityManager->flush();

        $this->addFlash('success', 'Job offer and all applications deleted successfully');
        return $this->redirectToRoute('admin_job_offers');
    }

    #[Route('/applications/{id}/delete', name: 'admin_application_delete', methods: ['POST'])]
    public function deleteApplication(ApplicationJob $application, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($application);
        $entityManager->flush();

        $this->addFlash('success', 'Application deleted successfully');
        return $this->redirectToRoute('admin_job_offers');
    }


    #[Route('/feedbacks', name: 'admin_feedback')]
    public function list(FeedbackRepository $feedbackRepository): Response
    {
        $feedbacks = $feedbackRepository->findAll();

        return $this->render('admin/feedback.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }
    #[Route('/{id}', name: 'admin_feedback_delete', methods: ['POST'])]
public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$feedback->getId(), $request->request->get('_token'))) {
        $entityManager->remove($feedback);
        $entityManager->flush();
    }

    return $this->redirectToRoute('admin_feedback', [], Response::HTTP_SEE_OTHER);
}

#[Route('/feedbacks/search', name: 'admin_feedback_search', methods: ['GET'])]
public function searchFeedbacks(Request $request, FeedbackRepository $feedbackRepository): JsonResponse
{
    $searchTerm = $request->query->get('q', '');

    $feedbacks = $feedbackRepository->createQueryBuilder('f')
        ->where('f.name LIKE :searchTerm')
        ->orWhere('f.subject LIKE :searchTerm')
        ->orWhere('f.message LIKE :searchTerm')
        ->setParameter('searchTerm', '%'.$searchTerm.'%')
        ->getQuery()
        ->getResult();

    $feedbacksArray = array_map(function($feedback) {
        return [
            'id' => $feedback->getId(),
            'name' => $feedback->getName(),
            'subject' => $feedback->getSubject(),
            'message' => $feedback->getMessage(),
        ];
    }, $feedbacks);

    return new JsonResponse($feedbacksArray);
}

    #[Route('/users/pdf', name: 'users_pdf')]
    public function generateUsersPdf(): Response
    {
        // Check if user is logged in and is admin
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        
        if (!$userId || $userRole != 3) {
            $this->addFlash('error', 'Access denied. Admin privileges required.');
            return $this->redirectToRoute('app_login');
        }
        
        // Get all users
        $users = $this->entityManager->getRepository(App_user::class)->findBy([], ['id_user' => 'DESC']);
        
        // Generate HTML content for PDF
        $html = $this->renderView('admin/pdf/users_pdf.html.twig', [
            'users' => $users,
            'date' => new \DateTime(),
        ]);
        
        // Initialize Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true); // If you need to execute PHP in HTML
        $dompdf = new Dompdf($options);

        // Load HTML content into Dompdf
        $dompdf->loadHtml($html);

        // Set paper size (A4 is the default)
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF (first pass to calculate dimensions, then render actual PDF)
        $dompdf->render();

        // Stream the generated PDF to the browser (or save to file if desired)
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="users-list.pdf"'
            ]
        );
    }

    #[Route('/user/view/{id}', name: 'admin_user_view')]
    public function viewUser(int $id): Response
    {
        $user = $this->entityManager->getRepository(App_user::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }
        
        return $this->render('admin/user_view.html.twig', [
            'user' => $user
        ]);
    }
    
    #[Route('/user/edit/{id}', name: 'admin_user_edit')]
    public function editUser(int $id, Request $request): Response
    {
        $user = $this->entityManager->getRepository(App_user::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }
        
        // Here you would normally handle the form submission for editing
        // For now, we'll just render the template
        
        return $this->render('admin/user_edit.html.twig', [
            'user' => $user
        ]);
    }
    
    #[Route('/user/delete/{id}', name: 'admin_user_delete')]
    public function deleteUser(int $id): Response
    {
        $user = $this->entityManager->getRepository(App_user::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }
        
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('admin_users');
    }
} 
