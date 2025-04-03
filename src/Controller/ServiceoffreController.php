<?php

namespace App\Controller;

use App\Entity\Serviceoffre;
use App\Form\ServiceoffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/serviceoffre')]
final class ServiceoffreController extends AbstractController
{
    #[Route('/', name: 'app_serviceoffre_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // The listing of service offers
        $serviceoffres = $entityManager->getRepository(Serviceoffre::class)->findAll();
    
        // The new form
        $serviceoffre = new Serviceoffre();
        $serviceoffre->setDatePosted(new \DateTime());
        $form = $this->createForm(ServiceoffreType::class, $serviceoffre);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData(); // Access startDate from the form data
            $endDate = $form->get('endDate')->getData(); // Access endDate from the form data
           
                   if ($startDate && $endDate) {
                       
           
                       // Calculate the duration in months
                       $interval = $startDate->diff($endDate);
                       $months = ($interval->y * 12) + $interval->m;
           
                       // Assign the appropriate duration category
                       if ($months < 1) {
                           $serviceoffre->setDuration('less than 1 month');
                       } elseif ($months >= 1 && $months <= 3) {
                           $serviceoffre->setDuration('1 to 3 months');
                       } elseif ($months > 3 && $months <= 6) {
                           $serviceoffre->setDuration('3 to 6 months');
                       } elseif($months > 6) {
                           $serviceoffre->setDuration('more than 6 months');
                       } 
                       else {
                           
                           $serviceoffre->setDuration('unknown');
                       }
                   }
            $entityManager->persist($serviceoffre);
            $entityManager->flush();
            return $this->redirectToRoute('app_serviceoffre_index');
        }
    
        return $this->render('serviceoffre/index.html.twig', [
            'serviceoffres' => $serviceoffres,
            'form' => $form->createView(), // pass the form to the view
        ]);
    }
    

    #[Route('/new', name: 'app_serviceoffre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $serviceoffre = new Serviceoffre();
         // Set the current date and time for date_posted
         $serviceoffre->setDatePosted(new \DateTime());

        $form = $this->createForm(ServiceoffreType::class, $serviceoffre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the form data for startDate and endDate (not from the entity)
 $startDate = $form->get('startDate')->getData(); // Access startDate from the form data
 $endDate = $form->get('endDate')->getData(); // Access endDate from the form data

        if ($startDate && $endDate) {
            

            // Calculate the duration in months
            $interval = $startDate->diff($endDate);
            $months = ($interval->y * 12) + $interval->m;

            // Assign the appropriate duration category
            if ($months < 1) {
                $serviceoffre->setDuration('less than 1 month');
            } elseif ($months >= 1 && $months <= 3) {
                $serviceoffre->setDuration('1 to 3 months');
            } elseif ($months > 3 && $months <= 6) {
                $serviceoffre->setDuration('3 to 6 months');
            } elseif($months > 6) {
                $serviceoffre->setDuration('more than 6 months');
            } 
            else {
                
                $serviceoffre->setDuration('unknown');
            }
        }
              

            $entityManager->persist($serviceoffre);
            $entityManager->flush();

            return $this->redirectToRoute('app_serviceoffre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('serviceoffre/new.html.twig', [
            'serviceoffre' => $serviceoffre,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idService}', name: 'app_serviceoffre_show', methods: ['GET'])]
    public function show(Serviceoffre $serviceoffre): Response
    {
        return $this->render('serviceoffre/show.html.twig', [
            'serviceoffre' => $serviceoffre,
        ]);
    }

    #[Route('/{idService}/edit', name: 'app_serviceoffre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Serviceoffre $serviceoffre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceoffreType::class, $serviceoffre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
 
 // Get the form data for startDate and endDate (not from the entity)
 $startDate = $form->get('startDate')->getData(); // Access startDate from the form data
 $endDate = $form->get('endDate')->getData(); // Access endDate from the form data

        if ($startDate && $endDate) {
            

            // Calculate the duration in months
            $interval = $startDate->diff($endDate);
            $months = ($interval->y * 12) + $interval->m;

            // Assign the appropriate duration category
            if ($months < 1) {
                $serviceoffre->setDuration('less than 1 month');
            } elseif ($months >= 1 && $months <= 3) {
                $serviceoffre->setDuration('1 to 3 months');
            } elseif ($months > 3 && $months <= 6) {
                $serviceoffre->setDuration('3 to 6 months');
            } elseif($months > 6) {
                $serviceoffre->setDuration('more than 6 months');
            } 
            else {
                
                $serviceoffre->setDuration('unknown');
            }
        }
            $entityManager->flush();

            return $this->redirectToRoute('app_serviceoffre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('serviceoffre/edit.html.twig', [
            'serviceoffre' => $serviceoffre,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idService}', name: 'app_serviceoffre_delete', methods: ['POST'])]
    public function delete(Request $request, Serviceoffre $serviceoffre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$serviceoffre->getIdService(), $request->request->get('_token'))) {
            $entityManager->remove($serviceoffre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_serviceoffre_index', [], Response::HTTP_SEE_OTHER);
    }
}
