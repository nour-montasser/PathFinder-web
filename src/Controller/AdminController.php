<?php

namespace App\Controller;

use App\Entity\Serviceoffre;
use App\Form\ServiceoffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/services', name: 'admin_services')]
    public function index(EntityManagerInterface $em): Response
    {
        $services = $em->getRepository(Serviceoffre::class)->findAll();

        return $this->render('admin/services.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/services/new', name: 'admin_services_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $service = new Serviceoffre();
        $form = $this->createForm(ServiceoffreType::class, $service);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $service->setDatePosted(new \DateTime());
            $em->persist($service);
            $em->flush();

            return $this->redirectToRoute('admin_services');
        }

        return $this->render('admin/service_form.html.twig', [
            'form' => $form->createView(),
            'editMode' => false
        ]);
    }

    #[Route('/services/{id}/edit', name: 'admin_services_edit')]
    public function edit(Serviceoffre $service, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ServiceoffreType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_services');
        }

        return $this->render('admin/service_form.html.twig', [
            'form' => $form->createView(),
            'editMode' => true
        ]);
    }

    #[Route('/services/{id}/delete', name: 'admin_services_delete', methods: ['POST'])]
    public function delete(Serviceoffre $service, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getIdService(), $request->request->get('_token'))) {
            $em->remove($service);
            $em->flush();
        }

        return $this->redirectToRoute('admin_services');
    }
}
