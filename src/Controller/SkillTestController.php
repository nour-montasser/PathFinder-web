<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SkillTestController extends AbstractController
{
    #[Route('/skill/test', name: 'app_skill_test')]
    public function index(): Response
    {
        return $this->render('skill_test/index.html.twig', [
            'controller_name' => 'SkillTestController',
        ]);
    }
}
