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
final class MessageController extends BaseController
{
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

        // Get channels with last message data
        $channels = $channelRepository->findChannelsWithLastMessage($user->getIdUser());

        // Get available users (excluding current user)
        $availableUsers = $channelRepository->findAvailableUsers($user->getIdUser());

        // Handle new channel creation
        if ($request->isMethod('POST')) {
            if ($request->request->has('selected_user')) {
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
            // Redirect to first channel if none selected
            $firstChannel = $channels[0]['channel'] ?? $channels[0]; // Handle both formats
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

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$message->getChannel() || !$message->getSender()) {
                $this->addFlash('error', 'Missing required message data');
                return $this->redirectToRoute('app_message_index', ['id_channel' => $id_channel]);
            }

            $entityManager->persist($message);
            $entityManager->flush();

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
        ChannelRepository $channelRepository,
        App_userRepository $userRepository,
        MessageRepository $messageRepository 

    ): Response {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        $channel = $message->getChannel();
        $id_channel = $channel->getId_channel();

        $channels = $channelRepository->findChannelsWithLastMessage($user->getIdUser());
        $availableUsers = $channelRepository->findAvailableUsers($user->getIdUser());
        $messages = $messageRepository->findBy(['channel' => $channel], ['time_sent' => 'ASC']);

        $editForm = $this->createForm(MessageType::class, $message);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_message_index', ['id_channel' => $id_channel]);
        }

        $newMessage = new Message();
        $newMessage->setChannel($channel);
        $newMessage->setSender($user);
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