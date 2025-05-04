<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProfileController extends BaseController
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

    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request): Response
    {
        // Get the current user
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to access this page');
            return $this->redirectToRoute('app_login');
        }

        $errors = [];
        $formSubmitted = false;

        if ($request->isMethod('POST')) {
            $formSubmitted = true;
            // Get form data
            $name = trim($request->request->get('name'));
            
            // Validate username
            if (empty($name)) {
                $errors['name'] = 'Username cannot be empty.';
            } elseif (strlen($name) < 3) {
                $errors['name'] = 'Username must be at least 3 characters long.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Username cannot exceed 50 characters.';
            } elseif (!preg_match('/^[a-zA-Z0-9\-_\s]+$/', $name)) {
                $errors['name'] = 'Username can only contain letters, numbers, spaces, hyphens and underscores.';
            } else {
                // Check if username already exists (except for current user)
                $existingUser = $this->entityManager->getRepository(App_user::class)
                    ->findOneBy(['name' => $name]);
                
                if ($existingUser && $existingUser->getId_user() !== $user->getId_user()) {
                    $errors['name'] = 'This username is already taken. Please choose another one.';
                }
            }

            // If no errors, update the profile
            if (empty($errors)) {
                // Update user information
                $user->setName($name);
                
                // Handle file upload
                $profileImage = $request->files->get('image');
                if ($profileImage) {
                    // Validate file type
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $originalExtension = strtolower(pathinfo($profileImage->getClientOriginalName(), PATHINFO_EXTENSION));
                    
                    if (!in_array($originalExtension, $allowedExtensions)) {
                        $this->addFlash('error', 'The image must be a JPG, JPEG, PNG or GIF file.');
                    } elseif ($profileImage->getSize() > 2000000) { // 2MB max size
                        $this->addFlash('error', 'The image size cannot exceed 2MB.');
                    } else {
                        $originalFilename = pathinfo($profileImage->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $this->slugger ? $this->slugger->slug($originalFilename) : strtolower(str_replace(' ', '_', $originalFilename));
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $originalExtension;
                        
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
                            
                            // Update session with new image
                            $session = $this->requestStack->getSession();
                            $session->set('user_image', $user->getImage());
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Failed to upload profile image: ' . $e->getMessage());
                        }
                    }
                }
                
                // Save changes
                $this->entityManager->flush();
                $this->addFlash('success', 'Profile updated successfully!');
                
                // Update session with new name
                $session = $this->requestStack->getSession();
                $session->set('user_name', $user->getName());
                
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'errors' => $errors,
            'formSubmitted' => $formSubmitted
        ]);
    }
}