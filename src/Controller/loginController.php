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


class loginController extends BaseController
{
    protected EntityManagerInterface $entityManager;
    protected RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
public function login(Request $request, EntityManagerInterface $entityManager): Response
{
    $error = null;
    $user = new App_user();
    $form = $this->createForm(App_userType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $email = $form->get('email')->getData();
        $plainPassword = $form->get('password')->getData();
        
        // Find user by email
        $user = $entityManager->getRepository(App_user::class)->findOneBy(['email' => $email]);

        // Verify password - IMPORTANT: You should use password hashing in your entity!
        if ($user && password_verify($plainPassword, $user->getPassword())) {
            $session = $request->getSession();
            $session->set('user_id', $user->getId_user());
            $session->set('user_name', $user->getName());
            $session->set('user_role', $user->getRole());
            $session->set('user_email', $user->getEmail());
            $session->set('user_image', $user->getImage());

            $this->addFlash('success', 'Welcome back, ' . $user->getName() . '!');
            return $this->redirectToRoute('app_home');
        } else {
            $error = 'Invalid email or password.';
        }
    } elseif ($form->isSubmitted() && !$form->isValid()) {
        $error = 'Please correct the errors in the form.';
    }

    return $this->render('login/login.html.twig', [
        'error' => $error,
        'form' => $form->createView(),
    ]);
}

    #[Route('/logout', name: 'app_logout')]
    public function logout(): RedirectResponse
    {
        $session = $this->requestStack->getSession();

        $session->clear();
        $session->invalidate();

        $this->addFlash('success', 'You have been logged out successfully.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email'));
            $name = trim($request->request->get('name'));
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
    
            // Validation
            if (!$email || !$name || !$password || !$confirmPassword) {
                $this->addFlash('error', 'All fields are required.');
                return $this->redirectToRoute('app_register');
            }
    
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Invalid email format.');
                return $this->redirectToRoute('app_register');
            }
    
            if (strlen($password) < 6) {
                $this->addFlash('error', 'Password must be at least 6 characters.');
                return $this->redirectToRoute('app_register');
            }
    
            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
                return $this->redirectToRoute('app_register');
            }
    
            try {
                // Check for existing user
                $existingUser = $this->entityManager->getRepository(App_user::class)
                    ->findOneBy(['email' => $email]);
    
                if ($existingUser) {
                    throw new \Exception('Email already registered.');
                }
    
                // Register user
                $user = new App_user();
                $user->setEmail($email);
                $user->setName($name);
                $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                $user->setRole(1);
                $user->setImage('default.png');
    
                $this->entityManager->persist($user);
                $this->entityManager->flush();
    
                $this->addFlash('success', 'Registration successful! Please log in.');
                return $this->redirectToRoute('app_login');
    
            } catch (\Exception $e) {
                $this->addFlash('error', 'Registration failed: ' . $e->getMessage());
                return $this->redirectToRoute('app_register');
            }
        }
    
        return $this->render('login/register.html.twig');
    }
    
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // In a real application:
            // 1. Generate reset token
            // 2. Save in database
            // 3. Send email with reset link

            $this->addFlash('success', 'If an account exists, a password reset link has been sent.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('login/forgot_password.html.twig');
    }
}
