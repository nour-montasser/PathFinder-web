<?php

namespace App\Controller;

use App\Entity\Applicationservice;
use App\Entity\Serviceoffre;
use App\Form\ApplicationserviceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/application/service')]
class ApplicationServiceController extends AbstractController
{
    #[Route('/', name: 'app_application_service_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $applicationservices = $entityManager
            ->getRepository(Applicationservice::class)
            ->findAll();

        return $this->render('application_service/index.html.twig', [
            'applicationservices' => $applicationservices,
        ]);
    }

    #[Route('/new/{idService}', name: 'app_application_service_new', methods: ['GET', 'POST'])]
public function new(
    Serviceoffre $serviceoffre,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $application = new Applicationservice();
    $application->setService($serviceoffre);

    // ✅ Get user from session
    $userId = $request->getSession()->get('mock_user_id');
    if ($userId) {
        $user = $entityManager->getRepository(\App\Entity\App_user::class)->find($userId);
        $application->setUser($user); // ✅ Link to logged-in user
    }

    $form = $this->createForm(ApplicationserviceType::class, $application);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($application);
        $entityManager->flush();

        return $this->redirectToRoute('app_serviceoffre_index');
    }

    return $this->render('application_service/new.html.twig', [
        'form' => $form->createView(),
        'serviceoffre' => $serviceoffre,
    ]);
}


    #[Route('/{idApp}', name: 'app_application_service_show', methods: ['GET'])]
    public function show(Applicationservice $applicationservice): Response
    {
        return $this->render('application_service/show.html.twig', [
            'applicationservice' => $applicationservice,
        ]);
    }

    #[Route('/{idApp}/edit', name: 'app_application_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Applicationservice $applicationservice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApplicationserviceType::class, $applicationservice);
        dump($applicationservice);
        $form->handleRequest($request);
        dump('Application saved'); 


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_application_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application_service/edit.html.twig', [
            'applicationservice' => $applicationservice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idApp}', name: 'app_application_service_delete', methods: ['POST'])]
    public function delete(Request $request, Applicationservice $applicationservice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $applicationservice->getIdApp(), $request->request->get('_token'))) {
            $entityManager->remove($applicationservice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_application_service_index', [], Response::HTTP_SEE_OTHER);
    }
}
