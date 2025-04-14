<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ApplicationJob;
use App\Entity\Job_offer;
use App\Repository\JobOfferRepository;
use App\Entity\Channel;
use App\Repository\ChannelRepository;
use App\Entity\Message;


#[Route('/admin')]
class AdminController extends BaseController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/cvs', name: 'admin_cvs')]
    public function cvs(): Response
    {
        return $this->render('admin/cvs.html.twig');
    }

    #[Route('/skill-tests', name: 'admin_skill_tests')]
    public function skillTests(): Response
    {
        return $this->render('admin/skill_tests.html.twig');
    }

    #[Route('/freelance', name: 'admin_freelance')]
    public function freelance(): Response
    {
        return $this->render('admin/freelance.html.twig');
    }

    #[Route('/job-offers', name: 'admin_job_offers')]
public function jobOffers(JobOfferRepository $jobOfferRepository): Response
{
    // Get all job offers with their applications
    $jobOffers = $jobOfferRepository->createQueryBuilder('j')
        ->leftJoin('j.applications', 'a')
        ->leftJoin('a.user', 'u')
        ->leftJoin('a.coverletter', 'c')
        ->addSelect('a', 'u', 'c')
        ->orderBy('j.date_posted', 'DESC')
        ->getQuery()
        ->getResult();

    return $this->render('admin/job_offers.html.twig', [
        'job_offers' => $jobOffers
    ]);
}

#[Route('/chats', name: 'admin_chats')]
public function chats(): Response
{
    return $this->redirectToRoute('admin_channels');
}


#[Route('/channels', name: 'admin_channels')]
public function channels(ChannelRepository $channelRepository, EntityManagerInterface $entityManager): Response
{
    // Get all channels with user info
    $channels = $channelRepository->createQueryBuilder('c')
        ->leftJoin('c.initiator', 'i')
        ->leftJoin('c.receiver', 'r')
        ->addSelect('i', 'r')
        ->orderBy('c.time_created', 'DESC')
        ->getQuery()
        ->getResult();

    // Get message counts and last message for each channel
    $messageRepo = $entityManager->getRepository(Message::class);
    
    foreach ($channels as $channel) {
        // Get message count
        $channel->messageCount = $messageRepo->count(['channel' => $channel]);
        
        // Get last message
        $channel->lastMessage = $messageRepo->findOneBy(
            ['channel' => $channel],
            ['time_sent' => 'DESC']
        );
    }

    return $this->render('admin/chats.html.twig', [
        'channels' => $channels
    ]);
}

#[Route('/channels/{id}/delete', name: 'admin_channel_delete', methods: ['POST'])]
public function deleteChannel(Channel $channel, ChannelRepository $channelRepository): Response
{
    $channelRepository->deleteChannelWithMessages($channel);
    
    $this->addFlash('success', 'Channel and all messages deleted successfully');
    return $this->redirectToRoute('admin_channels');
}


    #[Route('/job-offers/{id}/delete', name: 'admin_job_offer_delete', methods: ['POST'])]
public function deleteJobOffer(Job_offer $jobOffer, EntityManagerInterface $entityManager): Response
{
    // Delete all applications first
    foreach ($jobOffer->getApplications() as $application) {
        $entityManager->remove($application);
    }
    
    $entityManager->remove($jobOffer);
    $entityManager->flush();
    
    $this->addFlash('success', 'Job offer and all applications deleted successfully');
    return $this->redirectToRoute('admin_job_offers');
}

#[Route('/applications/{id}/delete', name: 'admin_application_delete', methods: ['POST'])]
public function deleteApplication(ApplicationJob $application, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($application);
    $entityManager->flush();
    
    $this->addFlash('success', 'Application deleted successfully');
    return $this->redirectToRoute('admin_job_offers');
}
}
