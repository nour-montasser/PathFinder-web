<?php

namespace App\Controller;

use Doctrine\ORM\Query\Expr\Base;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('/home/home.html.twig', [
            'title' => 'Dashboard',
            'web_title' => 'Pathfinder',
        ]);
    }
}
