<?php
// src/Controller/ChatController.php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ChannelRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat')]
    public function index(): Response
    {
        return $this->render('chat/ui-widgets-chatbox.html.twig', [
            'channel' => null, // Avoids template errors
            'messages' => [],
        ]);// Ensures the message loop doesn't break]);
    }}

