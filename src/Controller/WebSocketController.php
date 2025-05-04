<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;

class WebSocketController extends AbstractController
{
    #[Route('/ws/send', name: 'ws_send', methods: ['POST'])]
    public function sendMessage(string $message, int $channelId, int $userId): JsonResponse
{
    // If it's an AI command, let the WebSocket server handle it
    if (str_starts_with($message, '/pathfinderAI')) {
        return new JsonResponse(['status' => 'sent']);
    }

    $loop = Factory::create();
    $connector = new Connector($loop);
    
    $connector('ws://192.168.1.28:8082')
        ->then(function($conn) use ($message, $channelId, $userId) {
            $data = [
                'type' => 'message',
                'content' => $message,
                'channelId' => $channelId,
                'userId' => $userId
            ];
            $conn->send(json_encode($data));
            $conn->close();
        }, function($e) {
            error_log("Could not connect: {$e->getMessage()}");
        });
    
    $loop->run();
    
    return new JsonResponse(['status' => 'sent']);
}
}