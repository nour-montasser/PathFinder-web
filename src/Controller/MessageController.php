<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MessageRepository;
use App\Entity\Channel;
use App\Entity\App_user;
use App\Repository\ChannelRepository;
use App\Repository\App_userRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\DeepseekAIService;


#[Route('/message')]
final class MessageController extends BaseController
{
    #[Route('/{id_channel}', name: 'app_message_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
        ChannelRepository $channelRepository,
        App_userRepository $userRepository,
        DeepseekAIService $deepseekAI,
        int $id_channel = null
    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        // Get channels with last message data
        $channels = $channelRepository->findChannelsWithLastMessage($user->getIdUser());

        // Get available users (excluding current user)
        $availableUsers = $channelRepository->findAvailableUsers($user->getIdUser());

        // Handle new channel creation
        if ($request->isMethod('POST') && $request->request->has('selected_user')) {
            $selectedUserId = $request->request->get('selected_user');
            $receiver = $userRepository->find($selectedUserId);

            if (!$receiver) {
                $this->addFlash('error', 'Selected user not found');
                return $this->redirectToRoute('app_message_index');
            }

            // Check if channel already exists
            $existingChannel = $channelRepository->findOneBy([
                'initiator' => $user,
                'receiver' => $receiver
            ]) ?? $channelRepository->findOneBy([
                'initiator' => $receiver,
                'receiver' => $user
            ]);

            if ($existingChannel) {
                return $this->redirectToRoute('app_message_index', [
                    'id_channel' => $existingChannel->getId_channel()
                ]);
            }

            // Create new channel
            $channel = new Channel();
            $channel->setInitiator($user);
            $channel->setReceiver($receiver);
            $channel->setRating(0);
            $channel->setTime_Created(new \DateTime());

            $entityManager->persist($channel);
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [
                'id_channel' => $channel->getId_channel()
            ]);
        }

        // Initialize messages array
        $messages = [];
        $channel = null;

        // If a channel is selected, load its messages
        if ($id_channel) {
            $channel = $channelRepository->find($id_channel);
            if ($channel) {
                $messages = $messageRepository->findBy(
                    ['channel' => $channel],
                    ['time_sent' => 'ASC']
                );
            }
        } elseif (!empty($channels)) {
            // Redirect to first channel if none selected
            $firstChannel = $channels[0]['channel'] ?? $channels[0];
            return $this->redirectToRoute('app_message_index', [
                'id_channel' => $firstChannel->getId_channel()
            ]);
        }

        // Create new message form
        $message = new Message();
        if ($id_channel && $channel) {
            $message->setChannel($channel);
            $message->setSender($user);
            $message->setTimeSent(new \DateTime());
        }

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

       // In your form submission handler, replace this section:
if ($form->isSubmitted() && $form->isValid()) {
    /** @var UploadedFile $mediaFile */
    $mediaFile = $form->get('mediaFile')->getData();
    
    if ($mediaFile) {
        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads';
        $newFilename = uniqid().'.'.$mediaFile->guessExtension();
        
        try {
            $mediaFile->move($uploadDir, $newFilename);
            $message->setMedia($newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', 'File upload failed');
        }
    }
    
    $content = $message->getContent();
    if (str_starts_with($content, '/pathfinderAI')) {
        $prompt = trim(substr($content, strlen('/pathfinderAI')));
        
        $entityManager->persist($message);
        $entityManager->flush();
        
        $aiResponse = $deepseekAI->generateResponse($prompt);
        
        $aiMessage = new Message();
        $aiMessage->setChannel($channel);
        $aiMessage->setSender($this->getCurrentUser());
        $aiMessage->setTimeSent(new \DateTime());
        $aiMessage->setContent($aiResponse);
        
        $entityManager->persist($aiMessage);
        $entityManager->flush();
    } else {
        // Regular message handling
        $entityManager->persist($message);
        $entityManager->flush();
        
        // Send via WebSocket
        $this->forward('App\Controller\WebSocketController::sendMessage', [
            'message' => $message,
            'channelId' => $id_channel,
            'userId' => $user->getId_user()
        ]);
    }

    return $this->redirectToRoute('app_message_index', [
        'id_channel' => $id_channel
    ]);
}

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'channels' => $channels,
            'availableUsers' => $availableUsers,
            'currentUser' => $user,
            'selectedChannelId' => $id_channel,
        ]);
    }

    #[Route('/delete/{id_message}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $channel = $message->getChannel();

        if (!$channel) {
            throw $this->createNotFoundException('Channel not found for this message.');
        }

        if ($this->isCsrfTokenValid('delete' . $message->getIdMessage(), $request->request->get('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_message_index', [
            'id_channel' => $channel->getId_channel(),
        ]);
    }

    #[Route('/edit-inline/{id_message}', name: 'app_message_edit_inline', methods: ['GET', 'POST'])]
    public function editInline(
        Request $request,
        Message $message,
        EntityManagerInterface $entityManager,
        MessageRepository $messageRepository,
        ChannelRepository $channelRepository,
        App_userRepository $userRepository
    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        // Verify the current user owns this message
        if ($message->getSender()->getIdUser() !== $user->getIdUser()) {
            throw $this->createAccessDeniedException('You can only edit your own messages');
        }

        $channel = $message->getChannel();
        if (!$channel) {
            throw $this->createNotFoundException('Channel not found');
        }

        $id_channel = $channel->getId_channel();

        // Get data for sidebar
        $channels = $channelRepository->findChannelsWithLastMessage($user->getIdUser());
        $availableUsers = $channelRepository->findAvailableUsers($user->getIdUser());
        $messages = $messageRepository->findBy(
            ['channel' => $channel],
            ['time_sent' => 'ASC']
        );

        // Create edit form
        $editForm = $this->createForm(MessageType::class, $message, [
            'action' => $this->generateUrl('app_message_edit_inline', ['id_message' => $message->getIdMessage()])
        ]);
        
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // Update the timestamp
            $message->setTimeSent(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Message updated successfully');
            return $this->redirectToRoute('app_message_index', ['id_channel' => $id_channel]);
        }

        // Create new message form
        $newMessage = new Message();
        $newMessage->setChannel($channel);
        $newMessage->setSender($user);
        $newMessage->setTimeSent(new \DateTime());
        $form = $this->createForm(MessageType::class, $newMessage);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'editForm' => $editForm->createView(),
            'editingMessageId' => $message->getIdMessage(),
            'channels' => $channels,
            'availableUsers' => $availableUsers,
            'currentUser' => $user,
            'selectedChannelId' => $id_channel,
        ]);
    }

    #[Route('/channel/delete/{id_channel}', name: 'app_channel_delete', methods: ['POST'])]
    public function deleteChannel(
        Request $request,
        Channel $channel,
        ChannelRepository $channelRepository
    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        if ($channel->getInitiator()->getIdUser() !== $user->getIdUser() &&
            $channel->getReceiver()->getIdUser() !== $user->getIdUser()) {
            throw $this->createAccessDeniedException('You cannot delete this channel');
        }

        if ($this->isCsrfTokenValid('delete-channel' . $channel->getId_channel(), $request->request->get('_token'))) {
            $channelRepository->deleteChannelWithMessages($channel);
            $this->addFlash('success', 'Channel deleted successfully');
            return $this->redirectToRoute('app_message_index');
        }

        $this->addFlash('error', 'Invalid CSRF token');
        return $this->redirectToRoute('app_message_index', [
            'id_channel' => $channel->getId_channel()
        ]);
    }
}