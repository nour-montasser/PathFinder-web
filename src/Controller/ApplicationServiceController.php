<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApplicationServiceController extends AbstractController
{
    #[Route('/ApplicationService', name: 'app_service_applications')]
    public function index(): Response
    {
        return $this->render('application_service/index.html.twig', [
            'controller_name' => 'ApplicationServiceController',
        ]);
    }
}
