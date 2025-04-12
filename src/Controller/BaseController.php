<?php
namespace App\Controller;

use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

abstract class BaseController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack
    ) {}

    protected function getCurrentUser(): ?App_user
    {
        $userId = $this->requestStack->getSession()->get('user_id');
        if (!$userId) {
            return null;
        }
        
        return $this->entityManager->getRepository(App_user::class)
            ->findOneBy(['id_user' => $userId]); // Use your actual ID field name
    }

    protected function setCurrentUser(App_user $user): void
    {
        $this->requestStack->getSession()->set('user_id', $user->getId_user());
    }

    protected function ensureUserSession(): void
    {
        if (!$this->getCurrentUser()) {
            // Auto-set a default user (e.g., ID=1 for guest/anonymous)
            $defaultUser = $this->entityManager->getRepository(App_user::class)->find(2);
            if ($defaultUser) {
                $this->setCurrentUser($defaultUser);
            }
        }
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        // Automatically add current user to all templates
        $parameters['current_user'] = $this->getCurrentUser();
        return parent::render($view, $parameters, $response);
    }

}