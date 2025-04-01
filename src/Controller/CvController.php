<?php

namespace App\Controller;

use App\Entity\Cv;
use App\Form\CvType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CvController extends AbstractController
{
    #[Route('/cv', name: 'app_cv')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $cv = new Cv();
        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cv);
            $em->flush();

            $this->addFlash('success', 'CV created successfully!');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('cv/index.html.twig', [
            'cvForm' => $form->createView(),
            'controller_name' => 'CvController',
        ]);
    }
}
