<?php
namespace App\Controller;

use App\Service\CurrencyConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    #[Route('/convert-currency', name: 'convert_currency')]
public function convertCurrency(Request $request, CurrencyConverter $currencyConverter): JsonResponse
{
    $toCurrency = $request->query->get('to');

    if (!$toCurrency) {
        return new JsonResponse(['error' => 'No target currency provided'], 400);
    }

    $rates = $currencyConverter->getExchangeRates();

    if (!$rates || !isset($rates[$toCurrency])) {
        return new JsonResponse(['error' => 'Exchange rates not available or invalid currency'], 404);
    }

    $rate = $rates[$toCurrency];

    return new JsonResponse([
        'rate' => $rate,
        'currency' => $toCurrency
    ]);
}

}
