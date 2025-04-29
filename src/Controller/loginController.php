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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Label\Label;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\QrCodeGenerator;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;


class loginController extends BaseController
{
    protected EntityManagerInterface $entityManager;
    protected RequestStack $requestStack;
    private $connection;
    private $mailer;
    private $logger;
    private $qrCodeGenerator;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, Connection $connection, MailerInterface $mailer, LoggerInterface $logger, QrCodeGenerator $qrCodeGenerator)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->connection = $connection;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->qrCodeGenerator = $qrCodeGenerator;
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
            
            // Check for admin login
            if ($email === 'admin@pathfinder.tn') {
                // Special handling for admin
                $existingAdmin = $entityManager->getRepository(App_user::class)->findOneBy(['email' => $email]);
                
                if (!$existingAdmin) {
                    // Create admin user if it doesn't exist
                    $admin = new App_user();
                    $admin->setEmail('admin@pathfinder.tn');
                    $admin->setName('Administrator');
                    $admin->setPassword(password_hash('123456', PASSWORD_BCRYPT));
                    $admin->setRole(3); // 3 for admin
                    $admin->setImage('default.png');
                    
                    $entityManager->persist($admin);
                    $entityManager->flush();
                    
                    $existingAdmin = $admin;
                }
                
                // Verify admin password
                if (password_verify($plainPassword, $existingAdmin->getPassword())) {
                    $session = $request->getSession();
                    $session->set('user_id', $existingAdmin->getId_user());
                    $session->set('user_name', $existingAdmin->getName());
                    $session->set('user_role', $existingAdmin->getRole());
                    $session->set('user_email', $existingAdmin->getEmail());
                    $session->set('user_image', $existingAdmin->getImage());
                    
                    $this->addFlash('success', 'Welcome, Administrator!');
                    return $this->redirectToRoute('admin_dashboard');
                } else {
                    $error = 'Invalid admin password.';
                }
            } else {
                // Regular user login
                // Find user by email
                $user = $entityManager->getRepository(App_user::class)->findOneBy(['email' => $email]);

                // Verify password
                if ($user && password_verify($plainPassword, $user->getPassword())) {
                    $session = $request->getSession();
                    $session->set('user_id', $user->getId_user());
                    $session->set('user_name', $user->getName());
                    $session->set('user_role', $user->getRole());
                    $session->set('user_email', $user->getEmail());
                    $session->set('user_image', $user->getImage());

                    // Check if user is admin (role 3)
                    if ($user->getRole() == 3) {
                        $this->addFlash('success', 'Welcome, Administrator!');
                        return $this->redirectToRoute('admin_dashboard');
                    } else {
                        $this->addFlash('success', 'Welcome back, ' . $user->getName() . '!');
                        return $this->redirectToRoute('app_home');
                    }
                } else {
                    $error = 'Invalid email or password.';
                }
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
                $user->setRole(1); // Set default role to 1 for all users
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
        try {
            if ($request->isMethod('POST')) {
                $email = $request->request->get('email');
                $this->logger->info('Processing password reset request for email: ' . $email);

                // Check if email exists in database
                $user = $this->entityManager->getRepository(App_user::class)->findOneBy(['email' => $email]);
                
                if (!$user) {
                    $this->logger->warning('Password reset attempted for non-existent email: ' . $email);
                    $this->addFlash('error', 'Email address not found.');
                    return $this->redirectToRoute('app_forgot_password');
                }

                // Generate new password
                $newPassword = bin2hex(random_bytes(8));
                $this->logger->info('Generated new password for user');

                // Update password in database
                $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
                $this->entityManager->flush();
                $this->logger->info('Updated password in database');

                // Store password in session temporarily for QR code download
                $session = $request->getSession();
                $session->set('temp_password', $newPassword);

                // Send password reset email (without QR code)
                $this->sendPasswordResetEmail($email, $newPassword);

                $this->addFlash('success', 'A new password has been sent to your email address. Click the button below to download the QR code.');
                return $this->render('login/password_reset_success.html.twig', [
                    'email' => $email
                ]);
            }

            return $this->render('login/forgot_password.html.twig');
        } catch (\Exception $e) {
            $this->logger->error('Error in forgot password process: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while processing your request. Please try again.');
            return $this->redirectToRoute('app_forgot_password');
        }
    }

    #[Route('/download-password-qr', name: 'download_password_qr')]
    public function downloadPasswordQr(Request $request): Response
    {
        $session = $request->getSession();
        $password = $session->get('temp_password');
        
        if (!$password) {
            throw new \Exception('No password found for QR code generation');
        }

        // Generate QR code
        $qrCodeSvg = $this->qrCodeGenerator->createQrCode($password);

        // Clear the temporary password from session
        $session->remove('temp_password');

        // Return QR code as downloadable SVG file
        $response = new Response($qrCodeSvg);
        $response->headers->set('Content-Type', 'image/svg+xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="password-qr.svg"');
        
        return $response;
    }

    private function sendPasswordResetEmail(string $email, string $newPassword): void
    {
        try {
            $this->logger->info('Starting password reset process for email: ' . $email);

            // Generate QR code
            $qrCodeSvg = $this->qrCodeGenerator->createQrCode($newPassword);

            // Create email HTML template
            $emailHtml = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #333;">Password Reset</h2>
                    <p>Your new password has been generated:</p>
                    <div style="background-color: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px;">
                        <p style="color: #666; font-size: 14px;">
                        <strong>New Password Inside QR Code: download the QR code to see the password</strong>
                        </p>
                    </div>
                    <p>Qr code is attached to the email.</p>
                    <p style="color: #666; font-size: 14px;">
                        For security reasons, please change this password after logging in.<br>
                        If you did not request this password reset, please contact support immediately.
                    </p>
                </div>';

            $email = (new Email())
                ->from('PathFinder <azizowski10@gmail.com>')
                ->to($email)
                ->subject('Password Reset Request')
                ->html($emailHtml)
                ->attach($qrCodeSvg, 'password-qr.svg', 'image/svg+xml');

            $this->logger->info('Attempting to send password reset email with QR attachment');
            $this->mailer->send($email);
            $this->logger->info('Password reset email sent successfully');

        } catch (\Exception $e) {
            $this->logger->error('Failed to send password reset email: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to send password reset email: ' . $e->getMessage());
        }
    }

    #[Route('/test-qr', name: 'test_qr')]
    public function testQrCode(): Response
    {
        try {
            $testData = "Test QR Code Data";
            $qrCodeSvg = $this->qrCodeGenerator->createQrCode($testData);
            
            return new Response(
                '<html><body>
                    <h1>Test QR Code</h1>
                    <div style="background: white; padding: 20px;">
                        ' . $qrCodeSvg . '
                    </div>
                    <p>Data encoded: ' . htmlspecialchars($testData) . '</p>
                </body></html>',
                200,
                ['Content-Type' => 'text/html']
            );
        } catch (\Exception $e) {
            return new Response(
                'Error generating QR code: ' . $e->getMessage() . '<br>Trace: ' . $e->getTraceAsString(),
                500,
                ['Content-Type' => 'text/html']
            );
        }
    }
}
