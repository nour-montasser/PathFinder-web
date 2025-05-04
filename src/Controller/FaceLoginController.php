<?php

namespace App\Controller;


use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\App_userType;

use App\Repository\App_userRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FaceLoginController extends AbstractController
{
    #[Route('/face-login', name: 'face_login')]
    public function faceLogin(): Response
    {
        return $this->render('login/face_login.html.twig');
    }

    #[Route('/check-user', name: 'check_user', methods: ['POST'])]
    public function checkUser(App_userRepository $userRepository): JsonResponse
    {
        $detectedName = $_POST['detected_name'];

        // Check if the detected name matches a user in the database
        $user = $userRepository->findOneBy(['name' => $detectedName]);

        if ($user) {
            return new JsonResponse([
                'message' => 'User found',
                'user_details' => [
                    'id' => $user->getId_user(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                ],
                'user_found' => true
            ]);
        }

        return new JsonResponse([
            'message' => 'User not found',
            'user_found' => false
        ]);
    }

    #[Route('/login-user', name: 'login_user', methods: ['POST'])]
    public function loginUser(App_userRepository $userRepository, Request $request): JsonResponse
    {
        try {
            $detectedName = $request->get('detected_name'); // Get the detected name from the POST data
            $user = $userRepository->findOneBy(['name' => $detectedName]);
    
            if (!$user) {
                return new JsonResponse([
                    'login_success' => false,
                    'message' => 'User not found'
                ]);
            }
    
            // Log session data
            $sessionData = [
                'user_id' => $user->getId_user(),
                'user_name' => $user->getName(),
                'user_role' => $user->getRole(),
                'user_email' => $user->getEmail(),
                'user_image' => $user->getImage(),
            ];
    
            // Set session data
            $session = $request->getSession();
            $session->set('user_id', $sessionData['user_id']);
            $session->set('user_name', $sessionData['user_name']);
            $session->set('user_role', $sessionData['user_role']);
            $session->set('user_email', $sessionData['user_email']);
            $session->set('user_image', $sessionData['user_image']);
    
            return new JsonResponse([
                'login_success' => true,
                'message' => 'Login successful!',
                'session_data' => $sessionData // Returning session data to the frontend
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'login_success' => false,
                'message' => 'An error occurred during login.'
            ], 500);
        }
    }
        
    
}
