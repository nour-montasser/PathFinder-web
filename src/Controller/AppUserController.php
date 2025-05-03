<?php

namespace App\Controller;

use App\Entity\App_user;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class AppUserController extends AbstractController
{
    
    #[Route('/login/mock/{id}', name: 'mock_login')]
    public function mockLogin(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(App_user::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Mock user not found!');
        }

        // Reset session and store the new mock user ID
        $session = $request->getSession();
        $session->clear(); // Clear any previous session data
        $session->set('mock_user_id', $user->getId_user()());

        return $this->redirectToRoute('app_serviceoffre_index');
    }


    #[Route('/profile', name: 'app_user_profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getSessionUser($request, $em);

        if (!$user) {
            return $this->redirectToRoute('mock_login', ['id' => 1]); // fallback or redirect
        }

        return $this->render('app_user/profile.html.twig', [
            'user' => $user
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->invalidate();
        return $this->redirectToRoute('app_home');
    }


    public function getSessionUser(Request $request, EntityManagerInterface $em): ?App_user
    {
        $userId = $request->getSession()->get('mock_user_id');

        if (!$userId) {
            return null;
        }

        return $em->getRepository(App_user::class)->find($userId);
    }
}
