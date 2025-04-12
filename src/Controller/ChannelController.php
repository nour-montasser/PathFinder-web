<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\App_user;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/channel')]
final class ChannelController extends AbstractController
{
    #[Route('/', name: 'app_channel_index', methods: ['GET'])]
    public function index(ChannelRepository $channelRepository): Response
    {
        return $this->render('channel/index.html.twig', [
            'channels' => $channelRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_channel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user1 = $entityManager->getRepository(App_user::class)->find($request->request->get('user1_id'));
            $user2 = $entityManager->getRepository(App_user::class)->find($request->request->get('user2_id'));

            if (!$user1 || !$user2 || $user1 === $user2) {
                $this->addFlash('error', 'Invalid users selected.');
                return $this->redirectToRoute('app_channel_index');
            }

            $channel = new Channel();
            $channel->setIdUser1($user1);
            $channel->setIdUser2($user2);
            $channel->setTimeCreated(new \DateTime());
            $entityManager->persist($channel);
            $entityManager->flush();

            return $this->redirectToRoute('app_channel_index');
        }

        return $this->render('channel/new.html.twig');
    }

    #[Route('/{id_channel}', name: 'app_channel_show', methods: ['GET'])]
    public function show(Channel $channel): Response
    {
        return $this->render('channel/show.html.twig', [
            'channel' => $channel,
        ]);
    }

    #[Route('/delete/{id_channel}', name: 'app_channel_delete', methods: ['POST'])]
    public function delete(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $channel->getId_channel(), $request->request->get('_token'))) {
            $entityManager->remove($channel);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_channel_index');
    }
}
