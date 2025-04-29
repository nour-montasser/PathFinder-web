<?php

namespace App\Controller;

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