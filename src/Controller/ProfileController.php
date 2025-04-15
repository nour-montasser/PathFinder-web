<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\App_user;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProfileController extends BaseController
{
    protected EntityManagerInterface $entityManager;
    protected RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request): Response
    {
        // Get user ID from session
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        
        if (!$userId) {
            $this->addFlash('error', 'You must be logged in to access this page');
            return $this->redirectToRoute('app_login');
        }

        // Fetch user from database
        $user = $this->entityManager->getRepository(App_user::class)->find($userId);
        
        if (!$user) {
            $this->addFlash('error', 'User not found');
            return $this->redirectToRoute('app_login');
        }

        // Get or create profile
        $profile = $user->getProfile();
        if (!$profile) {
            $profile = new Profile();
            $profile->setUser($user);
            $user->setProfile($profile); // Maintain bidirectional relationship
        }

        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                $photoFile->move(
                    $this->getParameter('profile_photos_directory'),
                    $newFilename
                );
                $profile->setPhoto($newFilename);
            }

            $this->entityManager->persist($profile);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'profile' => $profile
        ]);
    }
}