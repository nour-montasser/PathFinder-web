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



#[Route('/message')]
final class MessageController extends BaseController{
    #[Route('/{id_channel}', name: 'app_message_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
        ChannelRepository $channelRepository,
        App_userRepository $userRepository,
        int $id_channel = null
    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
    
        // Fetch all channels for the current user
        $channels = [];
        $availableUsers = [];
        if ($user) {
            $channels = $channelRepository->findUserChannels($user->getIdUser());
            $availableUsers = $channelRepository->findAvailableUsers($user->getIdUser());
        }
    
        // Handle new channel creation
        if ($request->isMethod('POST') && $request->request->has('selected_user')) {
            $selectedUserId = $request->request->get('selected_user');
            $receiver = $userRepository->find($selectedUserId);
    
            // Check if channel already exists
            $existingChannel = $channelRepository->findOneBy([
                'initiator' => $user,
                'receiver' => $receiver
            ]) ?? $channelRepository->findOneBy([
                'initiator' => $receiver,
                'receiver' => $user
            ]);
    
            if ($existingChannel) {
                // Redirect to existing channel
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
        } else if (!empty($channels)) {
            // If no channel selected but channels exist, redirect to first channel
            return $this->redirectToRoute('app_message_index', [
                'id_channel' => $channels[0]->getId_channel()
            ]);
        }
    
        // Create new message form
        $message = new Message();
    if ($id_channel) {
        $channel = $channelRepository->find($id_channel);
        if ($channel) {
            $message->setChannel($channel);
        }
    }
    
    $form = $this->createForm(MessageType::class, $message);
    $form->handleRequest($request); // This processes the form submission

    if ($form->isSubmitted() && $form->isValid()) {
        // Set the sender and timestamp
        $message->setSender($user);
        $message->setTimeSent(new \DateTime());
        
        // Persist and flush the message
        $entityManager->persist($message);
        $entityManager->flush();

        // Redirect to prevent form resubmission
        return $this->redirectToRoute('app_message_index', [
            'id_channel' => $id_channel
        ]);
    }
        // Render the view
        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'channels' => $channels,
            'availableUsers' => $availableUsers, // Make sure to pass this
            'currentUser' => $user,
            'selectedChannelId' => $id_channel,
        ]);
    }
    

#[Route('/delete/{id_message}', name: 'app_message_delete', methods: ['POST'])]
public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
{
    // Store the channel BEFORE deleting the message
    $channel = $message->getChannel();

    if (!$channel) {
        throw $this->createNotFoundException('Channel not found for this message.');
    }

    // Validate the CSRF token
    if ($this->isCsrfTokenValid('delete' . $message->getIdMessage(), $request->request->get('_token'))) {
        $entityManager->remove($message);
        $entityManager->flush();

        // Redirect back to the message index with the channel ID
        return $this->redirectToRoute('app_message_index', [
            'id_channel' => $channel->getId_channel(),
        ], Response::HTTP_SEE_OTHER);
    }

    // Invalid CSRF, redirect without deleting
    return $this->redirectToRoute('app_message_index', [
        'id_channel' => $channel->getId_channel(),
    ]);
}

#[Route('/message/edit-inline/{id_message}', name: 'app_message_edit_inline', methods: ['GET', 'POST'])]
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
    
    // Get the channel from the message being edited
    $channel = $message->getChannel();
    $id_channel = $channel->getId_channel();
    
    // Get all channels and available users for the sidebar
    $channels = $channelRepository->findUserChannels($user->getIdUser());
    $availableUsers = $channelRepository->findAvailableUsers($user->getIdUser());
    
    // Get all messages for this channel
    $messages = $messageRepository->findBy(['channel' => $id_channel], ['time_sent' => 'ASC']);
    
    // Create the edit form
    $editForm = $this->createForm(MessageType::class, $message);
    $editForm->handleRequest($request);

    if ($editForm->isSubmitted() && $editForm->isValid()) {
        // Update the timestamp when editing
        $message->setTimeSent(new \DateTime());
        $entityManager->flush();
        
        return $this->redirectToRoute('app_message_index', ['id_channel' => $id_channel]);
    }

    // Create a new message form (for sending new messages)
    $newMessage = new Message();
    $newMessage->setChannel($channel);
    $form = $this->createForm(MessageType::class, $newMessage);

    return $this->render('message/index.html.twig', [
        'messages' => $messages,
        'form' => $form->createView(),
        'editForm' => $editForm->createView(),
        'editingMessageId' => $message->getIdMessage(),
        'channels' => $channels,
        'availableUsers' => $availableUsers, // Now properly passed
        'currentUser' => $user,
        'selectedChannelId' => $id_channel,
    ]);
}}