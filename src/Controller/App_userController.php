<?php
namespace App\Controller;

use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class App_userController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    #[Route('/switch-user/{id}', name: 'switch_user')]
    public function switchUser(int $id): Response
    {
        $user = $this->entityManager->find(App_user::class, $id);
        if ($user) {
            $this->requestStack->getSession()->set('user_id', $id);
            $this->addFlash('success', 'Switched to user '.$user->getName());
        }
        return $this->redirectToRoute('app_job_offer_index');
    }
}