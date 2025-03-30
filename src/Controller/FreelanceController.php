<?php

namespace App\Controller;

use App\Entity\Serviceoffre;
use App\Form\ServiceOffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/serviceoffre')]
final class FreelanceController extends AbstractController
{
    #[Route(name: 'app_service_offre_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $serviceOffres = $entityManager
            ->getRepository(Serviceoffre::class)
            ->findAll();

        return $this->render('serviceoffre/index.html.twig', [
            'service_offres' => $serviceOffres,
        ]);
    }

    #[Route('/new', name: 'app_service_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $serviceOffre = new Serviceoffre();
        // Set the current date and time as the posting date
        $serviceOffre->setDate_posted(new \DateTime("now"));

        $form = $this->createForm(ServiceOffreType::class, $serviceOffre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($serviceOffre);
            $entityManager->flush();

            return $this->redirectToRoute('app_service_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('serviceoffre/new.html.twig', [
            'service_offre' => $serviceOffre,
            'form' => $form,
        ]);
    }

    #[Route('/{id_service}', name: 'app_service_offre_show', methods: ['GET'])]
    public function show(Serviceoffre $serviceOffre): Response
    {
        return $this->render('serviceoffre/show.html.twig', [
            'service_offre' => $serviceOffre,
        ]);
    }

    #[Route('/{id_service}/edit', name: 'app_service_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Serviceoffre $serviceOffre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceOffreType::class, $serviceOffre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_service_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('serviceoffre/edit.html.twig', [
            'service_offre' => $serviceOffre,
            'form' => $form,
        ]);
    }

    #[Route('/{id_service}', name: 'app_service_offre_delete', methods: ['POST'])]
    public function delete(Request $request, Serviceoffre $serviceOffre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $serviceOffre->getId_service(), $request->request->get('_token'))) {
            $entityManager->remove($serviceOffre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_service_offre_index', [], Response::HTTP_SEE_OTHER);
    }
}
