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


#[Route('/admin')]
class AdminController extends BaseController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
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
}
use Dompdf\Dompdf;  // Add this line
use Dompdf\Options;  // Add this line
use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin', name: 'admin_')]
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

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        // Check if user is logged in and is admin
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        
        if (!$userId || $userRole != 3) { // 3 will be our admin role
            $this->addFlash('error', 'Access denied. Admin privileges required.');
            return $this->redirectToRoute('app_login');
        }
        
        // Get counts for dashboard stats
        $userCount = $this->entityManager->getRepository(App_user::class)->count(['role' => 1]); // Job seekers
        $enterpriseCount = $this->entityManager->getRepository(App_user::class)->count(['role' => 2]); // Enterprises
        
        return $this->render('admin/dashboard.html.twig', [
            'user_count' => $userCount,
            'enterprise_count' => $enterpriseCount,
        ]);
    }
    
    #[Route('/users', name: 'users')]
    public function users(): Response
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
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/users/view/{id}', name: 'user_view')]
    public function viewUser(int $id): Response
    {
        // Check if user is logged in and is admin
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        
        if (!$userId || $userRole != 3) {
            $this->addFlash('error', 'Access denied. Admin privileges required.');
            return $this->redirectToRoute('app_login');
        }
        
        // Find the user
        $user = $this->entityManager->getRepository(App_user::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }
        
        return $this->render('admin/user_view.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/users/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, int $id): Response
    {
        // Check if user is logged in and is admin
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        
        if (!$userId || $userRole != 3) {
            $this->addFlash('error', 'Access denied. Admin privileges required.');
            return $this->redirectToRoute('app_login');
        }
        
        // Find the user
        $user = $this->entityManager->getRepository(App_user::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }
        
        // Handle form submission
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $role = $request->request->get('role');
            $newPassword = $request->request->get('new_password');
            
            // Update user information
            $user->setName($name);
            $user->setEmail($email);
            $user->setRole((int)$role);
            
            // Update password if provided
            if ($newPassword) {
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
            }
            
            // Handle profile image upload
            $profileImage = $request->files->get('profile_image');
            if ($profileImage instanceof UploadedFile) {
                $originalFilename = pathinfo($profileImage->getClientOriginalName(), PATHINFO_FILENAME);
                $originalExtension = pathinfo($profileImage->getClientOriginalName(), PATHINFO_EXTENSION);
                $safeFilename = $this->slugger ? $this->slugger->slug($originalFilename) : strtolower(str_replace(' ', '_', $originalFilename));
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $originalExtension;
                
                try {
                    // Make sure upload directory exists
                    $uploadDir = 'uploads/users';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $profileImage->move(
                        $uploadDir,
                        $newFilename
                    );
                    $user->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload profile image: ' . $e->getMessage());
                }
            }
            
            // Save changes
            $this->entityManager->flush();
            
            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('admin_user_view', ['id' => $user->getId_user()]);
        }
        
        return $this->render('admin/user_edit.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/users/delete/{id}', name: 'user_delete')]
    public function deleteUser(int $id): Response
    {
        // Check if user is logged in and is admin
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');
        
        if (!$userId || $userRole != 3) {
            $this->addFlash('error', 'Access denied. Admin privileges required.');
            return $this->redirectToRoute('app_login');
        }
        
        // Find the user
        $user = $this->entityManager->getRepository(App_user::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }
        
        // Prevent deleting yourself
        if ($user->getId_user() === $userId) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('admin_users');
        }
        
        // Delete user
        $username = $user->getName(); // Store for flash message
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        $this->addFlash('success', "User '{$username}' has been deleted successfully.");
        return $this->redirectToRoute('admin_users');
    }



/*    #[Route('/users/pdf', name: 'users_pdf')]
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
        
        // Return response that will be handled by a PDF library
        // In a real implementation, you would use a library like Dompdf, TCPDF, or wkhtmltopdf
        // For now, we'll just return the HTML with appropriate headers for demonstration
        $response = new Response($html);
        // $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Type', 'text/html');  // Set content type to HTML
        $response->headers->set('Content-Disposition', 'attachment; filename="zebu.html"');
        
        // Note: In a real implementation, you would convert $html to PDF here
        // $pdfContent = $this->convertHtmlToPdf($html);
        // $response->setContent($pdfContent);
        
        return $response;
    }*/


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



    
} 
