<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LightcastTokenService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getAccessToken(): string
    {
        $response = $this->httpClient->request('POST', 'https://auth.emsicloud.com/connect/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'client_id' => 'i5w5e04419y5smiw',
                'client_secret' => 'k1i0Ps7r',
                'grant_type' => 'client_credentials',
                'scope' => 'emsi_open'
            ]
        ]);

        $data = $response->toArray(false);

        if (!isset($data['access_token'])) {
            throw new \RuntimeException('Failed to retrieve access token: ' . json_encode($data));
        }

        return $data['access_token'];
    }
}
