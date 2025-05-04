<?php

namespace App\Controller;

use App\Entity\App_user;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');
        $currentUser = null;

        if ($userId) {
            $currentUser = $this->entityManager->getRepository(App_user::class)->find($userId);
        }

        return $this->render('/home/home.html.twig', [
            'title' => 'Dashboard',
            'web_title' => 'Pathfinder',
            'currentUser' => $currentUser,
        ]);
    }
}
