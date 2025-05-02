<?php

namespace App\Controller;

use App\Entity\App_user;
use App\Entity\Payment;
use App\Repository\ApplicationserviceRepository;
use App\Repository\ServiceoffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/create-checkout-session/{id}', name: 'stripe_checkout_session')]
    public function createCheckoutSession(
        int $id,
        Request $request,
        ServiceoffreRepository $repo,
        ApplicationserviceRepository $appRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $application = $appRepo->find($id);

        if (!$application) {
            return new JsonResponse(['error' => 'Application not found'], 404);
        }

        $service = $application->getService();
        $amount = $application->getPriceOffre();

        $sessionUserId = $request->getSession()->get('mock_user_id');
        $user = $em->getRepository(App_user::class)->find($sessionUserId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $service->getTitle(),
                        'description' => $service->getDescription()
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'application_id' => $application->getIdApp(),
                'user_id' => $user->getIdUser(),
            ],
            'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_serviceoffre_manage', [
                'idService' => $service->getIdService()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(
        Request $request,
        EntityManagerInterface $em,
        ApplicationserviceRepository $appRepo
    ): Response {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            throw $this->createNotFoundException('Missing session ID');
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        $session = $stripe->checkout->sessions->retrieve($sessionId, []);

        $metadata = $session->metadata ?? null;
        $appId = $metadata['application_id'] ?? null;
        $userId = $metadata['user_id'] ?? null;

        if (!$appId || !$userId) {
            throw $this->createNotFoundException('Application or User ID not found in metadata');
        }

        $application = $appRepo->find($appId);
        $user = $em->getRepository(App_user::class)->find($userId);

        if (!$application || !$user) {
            throw $this->createNotFoundException('Invalid user or application reference');
        }

        // Prevent duplicate payment
        $existing = $em->getRepository(Payment::class)->findOneBy([
            'stripeSessionId' => $sessionId
        ]);

        if ($existing) {
            return $this->render('stripe/success.html.twig', [
                'payment' => $existing
            ]);
        }

        // Save payment record
        $payment = new Payment();
        $payment->setApplication($application);
        $payment->setUser($user);
        $payment->setAmount($application->getPriceOffre());
        $payment->setPaidAt(new \DateTime());
        $payment->setReceiptUrl($session->url ?? '');
        $payment->setStripeSessionId($sessionId);

        $em->persist($payment);

        // UPDATE application status to "paid"
        $application->setStatus('paid');

        $em->flush();

        return $this->render('stripe/success.html.twig', [
            'payment' => $payment
        ]);
    }

    #[Route('/payment/history', name: 'stripe_payment_history')]
    public function paymentHistory(EntityManagerInterface $em, Request $request): Response
    {
        $sessionUserId = $request->getSession()->get('mock_user_id');
        $user = $em->getRepository(App_user::class)->find($sessionUserId);

        if (!$user) {
            throw $this->createAccessDeniedException('User not found.');
        }

        $payments = $em->getRepository(Payment::class)->findBy([
            'user' => $user
        ], ['paidAt' => 'DESC']);

        return $this->render('stripe/history.html.twig', [
            'payments' => $payments
        ]);
    }

    #[Route('/payment/receipt/{id}', name: 'payment_receipt')]
public function viewReceipt(EntityManagerInterface $em, int $id): Response
{
    $payment = $em->getRepository(Payment::class)->find($id);

    if (!$payment) {
        throw $this->createNotFoundException('Payment not found.');
    }

    return $this->render('stripe/receipt.html.twig', [
        'payment' => $payment
    ]);
}

}
