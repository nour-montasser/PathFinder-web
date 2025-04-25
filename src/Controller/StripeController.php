<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Repository\ServiceoffreRepository;
use App\Repository\ApplicationserviceRepository; 

class StripeController extends AbstractController
{
    #[Route('/create-checkout-session/{id}', name: 'stripe_checkout_session')]
    public function createCheckoutSession(string $id, ServiceoffreRepository $repo,ApplicationServiceRepository $appRepo): JsonResponse
    {
        $id = (int) $id;
        $application = $appRepo->find($id);

    if (!$application) {
        return new JsonResponse(['error' => 'Application not found'], 404);
    }

    $service = $application->getService(); // assuming relationship exists
    $amount = $application->getPriceOffre();    // use price per application (not fixed!)

    Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    $session = \Stripe\Checkout\Session::create([
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
        'success_url' => $this->generateUrl('app_serviceoffre_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $this->generateUrl('app_serviceoffre_manage', ['idService' => $service->getIdService()], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return new JsonResponse(['id' => $session->id]);
}
}

