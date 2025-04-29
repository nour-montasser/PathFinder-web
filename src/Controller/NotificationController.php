<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends BaseController
{
    #[Route('/publish', name: 'publish_notification')]
    public function publish(HubInterface $hub)
    {
        $update = new Update(
            'https://example.com/skilltests', // topic
            json_encode(['message' => '🔥 New SkillTest Available!'])
        );

        $hub->publish($update);

        return $this->json(['status' => 'Message sent!']);
    }
}
