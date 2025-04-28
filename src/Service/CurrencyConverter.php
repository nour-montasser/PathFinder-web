<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyConverter
{
    private HttpClientInterface $client;
    private const API_URL = 'https://v6.exchangerate-api.com/v6/01b62ddd85a1a7a3b14cb327/latest/USD';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getExchangeRates(): ?array
    {
        try {
            $response = $this->client->request('GET', self::API_URL);
            $data = $response->toArray();

            if (isset($data['conversion_rates'])) {
                return $data['conversion_rates'];
            }

            return null;

        } catch (\Exception $e) {
            // Log error if you want
            return null;
        }
    }

    public function convert(float $amount, string $toCurrency, array $rates): ?float
    {
        if (!isset($rates[$toCurrency])) {
            return null; // Currency not available
        }

        return $amount * $rates[$toCurrency];
    }
}
