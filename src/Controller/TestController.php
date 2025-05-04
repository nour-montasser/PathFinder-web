<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        return new Response('Test controller is working!');
    }
    
    #[Route('/test/google', name: 'app_test_google')]
    public function testGoogle(): Response
    {
        return new Response('Google test route is working!');
    }
} 