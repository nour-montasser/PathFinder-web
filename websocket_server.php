<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;


require __DIR__.'/vendor/autoload.php';

class MessageWebSocketServer implements MessageComponentInterface {
    protected $clients;
    private $entityManager;
    private $subscribedChannels = [];
    private $userConnections = [];

        public function __construct($entityManager) {
            $this->clients = new \SplObjectStorage;
            $this->entityManager = $entityManager;
        }
    

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            if ($data === null) throw new \Exception("Invalid JSON");
            
            // Add ping-pong handling
            if ($data['type'] === 'ping') {
                $from->send(json_encode(['type' => 'pong']));
                return;
            }
    
            echo "Received: " . $msg . "\n";
    
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
        
        $from->send(json_encode([
            'type' => 'authenticated',
            'userId' => $from->userId
        ]));
    }

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
    }

    // In the handleMessage method, add validation:
private function handleMessage($from, $data) {
    if (!isset($from->channelId) || !isset($from->userId)) {
        throw new \InvalidArgumentException("Not subscribed to any channel or not authenticated");
    }

    if (!isset($data['content']) || empty(trim($data['content']))) {
        throw new \InvalidArgumentException("Message content cannot be empty");
    }

    try {
        $channel = $this->entityManager->find('App\Entity\Channel', $from->channelId);
        $sender = $this->entityManager->find('App\Entity\App_user', $from->userId);

        if (!$channel || !$sender) {
            throw new \InvalidArgumentException("Invalid channel or sender");
        }

        $message = new \App\Entity\Message();
        $message->setContent(trim($data['content']));
        $message->setSender($sender);
        $message->setChannel($channel);
        $message->setTimeSent(new \DateTime());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

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

        // Broadcast to all subscribed clients in this channel
        if (isset($this->subscribedChannels[$from->channelId])) {
            foreach ($this->subscribedChannels[$from->channelId] as $client) {
                $client->send(json_encode($response));
            }
        }
    } catch (\Exception $e) {
        error_log("Error handling message: " . $e->getMessage());
        $from->send(json_encode([
            'type' => 'error',
            'message' => $e->getMessage()
        ]));
    }
}
    

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        echo "Connection {$conn->resourceId} disconnected\n";
        
        if (isset($conn->userId)) {
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

// Run the server on port 8082
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MessageWebSocketServer($entityManager)
        )
    ),
    8082,
    '0.0.0.0'
);

echo "WebSocket server running on ws://0.0.0.0:8082\n";
$server->run();