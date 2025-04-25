<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeonamesService
{
    private $httpClient;
    private $username;

    public function __construct(HttpClientInterface $httpClient, string $geonamesUsername)
    {
        $this->httpClient = $httpClient;
        $this->username = $geonamesUsername;
    }

    public function fetchCountryMap(): array
    {
        $response = $this->httpClient->request('GET', 'http://api.geonames.org/countryInfoJSON', [
            'query' => [
                'username' => $this->username
            ]
        ]);
    
        $data = $response->toArray();
        $countryMap = [];
    
        foreach ($data['geonames'] as $country) {
            $countryMap[$country['countryName']] = $country['countryCode'];
        }
    
        ksort($countryMap);
    
        return $countryMap;
    }
    
    

    public function fetchCities(string $countryCode = ''): array
    {
        $query = [
            'username' => $this->username,
            'maxRows' => 100,
            'featureClass' => 'P' // Populated places
        ];

        if (!empty($countryCode)) {
            $query['country'] = $countryCode;
        }

        try {
            $response = $this->httpClient->request('GET', 'http://api.geonames.org/searchJSON', [
                'query' => $query
            ]);

            $data = $response->toArray();
            
            if (!isset($data['geonames'])) {
                return [];
            }

            return array_map(fn($city) => $city['name'], $data['geonames']);
        } catch (\Exception $e) {
            // Log error if needed
            return [];
        }
    }


    public function fetchCityCoordinates(string $cityName, string $countryCode = null): ?array
{
    $query = [
        'name' => $cityName,
        'username' => $this->username,
        'maxRows' => 1,
        'featureClass' => 'P' // Populated places
    ];

    if ($countryCode) {
        $query['country'] = $countryCode;
    }

    $response = $this->httpClient->request('GET', 'http://api.geonames.org/searchJSON', [
        'query' => $query
    ]);

    $data = $response->toArray();

    if (empty($data['geonames'])) {
        return null;
    }

    $city = $data['geonames'][0];
    return [
        'name' => $city['name'],
        'lat' => (float) $city['lat'],
        'lng' => (float) $city['lng'],
        'countryCode' => $city['countryCode']
    ];
}

  
}