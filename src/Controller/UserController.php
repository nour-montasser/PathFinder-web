<?php

namespace App\Controller;

use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile', name: 'app_profile_')]
class UserController extends BaseController
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

    #[Route('/', name: 'view')]
    public function viewProfile(): Response
    {
        // Get current user
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view your profile.');
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request): Response
    {
        // Get current user
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to edit your profile.');
            return $this->redirectToRoute('app_login');
        }
        
        // Handle form submission
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            // Basic validation
            if (empty($name)) {
                $this->addFlash('error', 'Name cannot be empty.');
                return $this->redirectToRoute('app_profile_edit');
            }
            
            // Update name
            $user->setName($name);
            
            // Password change logic
            if (!empty($newPassword)) {
                // Verify current password
                if (!password_verify($currentPassword, $user->getPassword())) {
                    $this->addFlash('error', 'Current password is incorrect.');
                    return $this->redirectToRoute('app_profile_edit');
                }
                
                // Verify confirmation
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'New passwords do not match.');
                    return $this->redirectToRoute('app_profile_edit');
                }
                
                // Update password
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
            }
            
            // Handle profile image upload
            $profileImage = $request->files->get('profile_image');
            if ($profileImage instanceof UploadedFile) {
                $originalFilename = pathinfo($profileImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger ? $this->slugger->slug($originalFilename) : strtolower(str_replace(' ', '_', $originalFilename));
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profileImage->guessExtension();
                
                try {
                    // Define your upload directory - ensure it exists and is writable
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
            
            // Update session with new name and image
            $session = $this->requestStack->getSession();
            $session->set('user_name', $user->getName());
            $session->set('user_image', $user->getImage());
            
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_profile_view');
        }
        
        return $this->render('user/edit_profile.html.twig', [
            'user' => $user,
        ]);
    }
} 