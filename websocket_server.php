<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use App\Service\DeepseekAIService;
use Symfony\Component\HttpClient\HttpClient;



require __DIR__.'/vendor/autoload.php';

class MessageWebSocketServer implements MessageComponentInterface {
    protected $clients;
    private $entityManager;
    private $subscribedChannels = [];
    private $userConnections = [];
    protected $onlineUsers = [];
    
private $deepseekAI;

public function __construct($entityManager, $deepseekAI) {
    $this->clients = new \SplObjectStorage;
    $this->entityManager = $entityManager;
    $this->deepseekAI = $deepseekAI;
}

    

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            if ($data === null) throw new \Exception("Invalid JSON");
            
            switch ($data['type']) {
                case 'auth':
                    $this->handleAuth($from, $data);
                    break;
                case 'subscribe':
                    $this->handleSubscribe($from, $data);
                    break;
                case 'message':
                    $this->handleMessage($from, $data);
                    break;
                case 'messageSeen':
                    $this->handleMessageSeen($from, $data);
                    break;
                
                case 'ping':
                    $from->send(json_encode(['type' => 'pong']));
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown message type");
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }

    private function handleAuth($from, $data) {
        if (!isset($data['userId'])) {
            throw new \InvalidArgumentException("Missing userId");
        }

        $from->userId = (int)$data['userId'];
        $this->userConnections[$from->userId] = $from;
        
        // Mark user as online
        $this->onlineUsers[$from->userId] = true;

        // First, broadcast to everyone that this user is online
        $this->broadcastUserStatus($from->userId, true);
        
        // Then, send the current online status of all users to the newly connected user
        foreach ($this->onlineUsers as $userId => $status) {
            $from->send(json_encode([
                'type' => 'userStatus',
                'userId' => $userId,
                'isOnline' => true
            ]));
        }

        $from->send(json_encode([
            'type' => 'authenticated',
            'userId' => $from->userId
        ]));
    }

    // Add this property
    private $seenMessages = [];

    // Add this method to handle seen status
    private function handleMessageSeen($from, $data) {
        if (!isset($data['messageId']) || !isset($from->channelId)) {
            throw new \InvalidArgumentException("Missing messageId or not subscribed to channel");
        }

        $response = [
            'type' => 'messageSeen',
            'messageId' => $data['messageId'],
            'seenBy' => $from->userId,
            'channelId' => $from->channelId
        ];

        // Broadcast to all users in the channel
        if (isset($this->subscribedChannels[$from->channelId])) {
            foreach ($this->subscribedChannels[$from->channelId] as $client) {
                $client->send(json_encode($response));
            }
        }
    }

    // Modify handleSubscribe method
    private function handleSubscribe($from, $data) {
        if (!isset($data['channelId']) || !isset($from->userId)) {
            throw new \InvalidArgumentException("Missing channelId or not authenticated");
        }

        $from->channelId = (int)$data['channelId'];
        
        if (!isset($this->subscribedChannels[$from->channelId])) {
            $this->subscribedChannels[$from->channelId] = [];
        }
        
        $this->subscribedChannels[$from->channelId][$from->userId] = $from;
        
        echo "User {$from->userId} subscribed to channel {$from->channelId}\n";
        $from->send(json_encode([
            'type' => 'subscribed',
            'channelId' => $from->channelId
        ]));

        // Mark all unread messages as seen when user joins channel
        $this->handleChannelOpen($from);
    }

    // Add new method to handle channel open
    private function handleChannelOpen($from) {
        $response = [
            'type' => 'channelSeen',
            'channelId' => $from->channelId,
            'seenBy' => $from->userId
        ];

        // Broadcast to all users in the channel
        if (isset($this->subscribedChannels[$from->channelId])) {
            foreach ($this->subscribedChannels[$from->channelId] as $client) {
                $client->send(json_encode($response));
            }
        }
    }

    // In the handleMessage method, add validation:
    private function handleMessage($from, $data) {
        error_log("handleMessage received: " . print_r($data, true)); // Debug the incoming data
        
        if (!isset($from->channelId) || !isset($from->userId)) {
            error_log("Missing channelId or userId"); // Debug missing fields
            throw new \InvalidArgumentException("Not subscribed to any channel or not authenticated");
        }
    
        if (!isset($data['content']) || empty(trim($data['content']))) {
            error_log("Empty content received"); // Debug empty content
            throw new \InvalidArgumentException("Message content cannot be empty");
        }
    
        try {
            $content = trim($data['content']);
            error_log("Processing content: " . $content); // Debug the content
            
            // Add specific debug for AI commands
            if (str_starts_with($content, '/pathfinderAI')) {
                error_log("AI command detected: " . $content); // Debug AI command
                $prompt = trim(substr($content, strlen('/pathfinderAI')));
                error_log("Extracted prompt: " . $prompt); // Debug the extracted prompt
                
                $channel = $this->entityManager->find('App\Entity\Channel', $from->channelId);
                $sender = $this->entityManager->find('App\Entity\App_user', $from->userId);
    
                if (!$channel || !$sender) {
                    error_log("Invalid channel or sender"); // Debug invalid entities
                    throw new \InvalidArgumentException("Invalid channel or sender");
                }
    
                // Create the user's message
                $userMessage = new \App\Entity\Message();
                $userMessage->setContent($content);
                $userMessage->setSender($sender);
                $userMessage->setChannel($channel);
                $userMessage->setTimeSent(new \DateTime());
                $this->entityManager->persist($userMessage);
                
                error_log("User message persisted"); // Debug persistence
                
                // Generate AI response
                error_log("Generating AI response..."); // Debug before AI call
                $aiResponse = $this->generateAIResponse($prompt);
                error_log("AI response: " . substr($aiResponse, 0, 50) . "..."); // Debug AI response
                
                $aiMessage = new \App\Entity\Message();
                $aiMessage->setContent($aiResponse);
                $aiMessage->setSender($sender);
                $aiMessage->setChannel($channel);
                $aiMessage->setTimeSent(new \DateTime());
                $this->entityManager->persist($aiMessage);
                
                $this->entityManager->flush();
                error_log("Messages flushed to database"); // Debug after flush
                
                // Broadcast both messages
                $this->broadcastMessage($userMessage, $channel, $sender);
                $this->broadcastMessage($aiMessage, $channel, $sender);
                
                error_log("Messages broadcasted"); // Debug after broadcast
                return;
            }
    
            // Normal message handling
            error_log("Processing normal message"); // Debug normal message flow
            
            $channel = $this->entityManager->find('App\Entity\Channel', $from->channelId);
            $sender = $this->entityManager->find('App\Entity\App_user', $from->userId);
    
            if (!$channel || !$sender) {
                error_log("Invalid channel or sender in normal message"); // Debug invalid entities
                throw new \InvalidArgumentException("Invalid channel or sender");
            }
    
            $message = new \App\Entity\Message();
            $message->setContent($content);
            $message->setSender($sender);
            $message->setChannel($channel);
            $message->setTimeSent(new \DateTime());
    
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            error_log("Normal message persisted and flushed"); // Debug persistence
    
            $this->broadcastMessage($message, $channel, $sender);
            error_log("Normal message broadcasted"); // Debug after broadcast
    
        } catch (\Exception $e) {
            error_log("Error in handleMessage: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }
    
    private function broadcastMessage($message, $channel, $sender) {
        $response = [
            'type' => 'message',
            'id' => $message->getIdMessage(),
            'content' => $message->getContent(),
            'sender' => [
                'id' => $sender->getId_user(),
                'name' => $sender->getName(),
                'image' => $sender->getImage()
            ],
            'timeSent' => $message->getTimeSent()->format('H:i'),
            'channelId' => $channel->getId_channel()
        ];
    
        if (isset($this->subscribedChannels[$channel->getId_channel()])) {
            foreach ($this->subscribedChannels[$channel->getId_channel()] as $client) {
                $client->send(json_encode($response));
            }
        }
    }
    
    private function generateAIResponse($prompt) {
        try {
            error_log("Attempting to generate AI response for prompt: " . $prompt);
            
            if (!$this->deepseekAI) {
                error_log("DeepseekAIService is not initialized!");
                return "AI service is currently unavailable.";
            }
            
            $response = $this->deepseekAI->generateResponse($prompt);
            error_log("Successfully received AI response");
            return $response;
        } catch (\Exception $e) {
            error_log("AI Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return "Sorry, I couldn't generate a response. Please try again later.";
        }
    }
    
    private function broadcastUserStatus($userId, $isOnline) {
        $response = [
            'type' => 'userStatus',
            'userId' => $userId,
            'isOnline' => $isOnline
        ];
        
        // Broadcast to all connected clients
        foreach ($this->clients as $client) {
            $client->send(json_encode($response));
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        echo "Connection {$conn->resourceId} disconnected\n";
        
        if (isset($conn->userId)) {
            // Remove from online users and broadcast offline status
            unset($this->onlineUsers[$conn->userId]);
            $this->broadcastUserStatus($conn->userId, false);
            unset($this->userConnections[$conn->userId]);
        }
        
        if (isset($conn->channelId)) {
            if (isset($this->subscribedChannels[$conn->channelId][$conn->userId])) {
                unset($this->subscribedChannels[$conn->channelId][$conn->userId]);
                
                if (empty($this->subscribedChannels[$conn->channelId])) {
                    unset($this->subscribedChannels[$conn->channelId]);
                }
            }
        }
        
        $this->clients->detach($conn);
    }
    
    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "Error for connection {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }
}

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__."/src/Entity"],
    isDevMode: true,
);
$connectionParams = [
    'dbname' => 'pathfinder',
    'user' => 'root',
    'password' => '',
    'host' => '127.0.0.1',
    'port' => 3307,
    'driver' => 'pdo_mysql',
    'charset' => 'utf8mb4',
    'serverVersion' => '10.4.28-MariaDB'
];


$connection = DriverManager::getConnection($connectionParams, $config);

$entityManager = new EntityManager($connection, $config);
$httpClient = HttpClient::create();
$deepseekAI = new DeepseekAIService($httpClient, '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');
// Run the server on port 8082
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MessageWebSocketServer($entityManager, $deepseekAI)
        )
    ),
    8082,
    '0.0.0.0'
);

echo "WebSocket server running on ws://0.0.0.0:8082\n";
$server->run();