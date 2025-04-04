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



#[Route('/message')]
final class MessageController extends AbstractController{
    #[Route('/', name: 'app_message_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set current time
            $message->setTimeSent(new \DateTime());

            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index');
        }

        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = new Message();
        $message->setTimeSent(new \DateTime("now"));

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id_message}', name: 'app_message_show', methods: ['GET'])]
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id_message}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id_message}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getIdMessage(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/message/edit-inline/{id_message}', name: 'app_message_edit_inline', methods: ['GET'])]
    public function editInline(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createForm(MessageType::class, $message);

        return $this->render('message/index.html.twig', [
            'messages' => $entityManager->getRepository(Message::class)->findAll(),
            'form' => $this->createForm(MessageType::class, new Message())->createView(),
            'editForm' => $editForm->createView(),
        ]);
    }

    #[Route('/message/{id_message}', name: 'app_message_update', methods: ['POST'])]
    public function update(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_message_index');
        }

        // Handle invalid form submission
        return $this->redirectToRoute('app_message_edit_inline', ['id_message' => $message->getIdMessage()]);
    }
}
