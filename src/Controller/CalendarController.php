<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends BaseController
{
    #[Route('/calendar', name: 'calendar_page')]
    public function calendarPage(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('serviceoffre/calendar.html.twig');
    }

    #[Route('/your-calendar-events-endpoint', name: 'calendar_events')]
    public function calendarEvents(Request $request): JsonResponse
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $events = $session->get('calendar_events', []);

        return $this->json($events);
    }

    #[Route('/calendar-event-create', name: 'calendar_event_create', methods: ['POST'])]
public function createEvent(Request $request): JsonResponse
{
    $session = $request->getSession();
    if (!$session->isStarted()) {
        $session->start();
    }

    $events = $session->get('calendar_events', []);
    $data = $request->request->all(); // ✅ FIXED!

    if (!isset($data['title'], $data['start'], $data['end'])) {
        return $this->json(['error' => 'Invalid event data'], 400);
    }

    $newEvent = [
        'id' => uniqid(), // unique ID
        'title' => $data['title'],
        'start' => $data['start'],
        'end' => $data['end'],
    ];

    $events[] = $newEvent;
    $session->set('calendar_events', $events);

    return $this->json(['message' => 'Event created', 'data' => $newEvent], 201);
}

    #[Route('/calendar-event-update', name: 'calendar_event_update', methods: ['POST'])]
    public function updateEvent(Request $request): JsonResponse
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $events = $session->get('calendar_events', []);
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id'], $data['start'])) {
            return $this->json(['error' => 'Invalid event update'], 400);
        }

        foreach ($events as &$event) {
            if ($event['id'] === $data['id']) {
                $event['start'] = $data['start'];
                $event['end'] = $data['end'] ?? $event['start'];
                break;
            }
        }

        $session->set('calendar_events', $events);

        return $this->json(['message' => 'Event updated', 'data' => $data]);
    }
}
