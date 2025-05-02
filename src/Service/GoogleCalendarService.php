<?php
namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventReminder;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceData;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleCalendarService
{
    private $params;
    private $client;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->client = new Client();
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        $this->client->setApplicationName('PathFinder Local Dev');
        $this->client->setScopes([Calendar::CALENDAR]);
        $this->client->setRedirectUri('http://localhost:8000/application/job/google/auth/callback');
        $this->client->setAuthConfig($this->params->get('kernel.project_dir').'/config/google_credentials.json');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    
        // Load previously authorized token if it exists
        $tokenPath = $this->params->get('kernel.project_dir').'/config/google_token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }
    }

    public function scheduleInterview(
        string $organizerEmail,
        string $attendeeEmail,
        string $jobTitle,
        \DateTime $startTime = null,
        int $durationMinutes = 60
    ): array {
        try {
            $calendar = new Calendar($this->client);
            
            // If no start time provided, schedule for now
            $startTime = $startTime ?? new \DateTime('now', new \DateTimeZone('UTC'));
            $endTime = (clone $startTime)->add(new \DateInterval("PT{$durationMinutes}M"));

            $event = new Event([
                'summary' => "Interview for {$jobTitle}",
                'description' => "Job interview for {$jobTitle} position",
                'start' => new EventDateTime([
                    'dateTime' => $startTime->format(\DateTimeInterface::RFC3339),
                    'timeZone' => $startTime->getTimezone()->getName(),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $endTime->format(\DateTimeInterface::RFC3339),
                    'timeZone' => $endTime->getTimezone()->getName(),
                ]),
                'attendees' => [
                    ['email' => $organizerEmail],
                    ['email' => $attendeeEmail],
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        new EventReminder(['method' => 'email', 'minutes' => 24 * 60]),
                        new EventReminder(['method' => 'popup', 'minutes' => 10]),
                    ],
                ],
                'conferenceData' => new ConferenceData([
                    'createRequest' => new CreateConferenceRequest([
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ]),
                ]),
            ]);

            $event = $calendar->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
            ]);

            return [
                'success' => true,
                'meetLink' => $event->getHangoutLink(),
                'calendarLink' => $event->getHtmlLink(),
                'startTime' => $startTime,
                'endTime' => $endTime,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }
    public function getAuthUrl(): string
{
    return $this->client->createAuthUrl();
}
public function handleAuthCallback(string $code): void
{
    $token = $this->client->fetchAccessTokenWithAuthCode($code);
    $this->client->setAccessToken($token);

    // Save the token for future use
    $tokenPath = $this->params->get('kernel.project_dir').'/config/google_token.json';
    file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
}
}