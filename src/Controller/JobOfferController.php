<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class JobOfferController extends AbstractController
{
    #[Route('/jobOffer', name: 'app_job_offer')]
    public function index(): Response
    {
        return $this->render('job_offer/index.html.twig', [
            'controller_name' => 'JobOfferController',
        ]);
    }
}