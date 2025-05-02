<?php
namespace App\Controller;

use App\Entity\Coverletter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\CoverLetterType;

#[Route('/coverletter')]
class CoverLetterController extends AbstractController
{
    #[Route('/{id}/edit', name: 'app_coverletter_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Coverletter $coverletter,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CoverLetterType::class, [
            'subject' => $coverletter->getSubject(),
            'content' => $coverletter->getContent()
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $coverletter->setSubject($data['subject'])
                       ->setContent($data['content']);
            
            $entityManager->flush();
            
            return $this->redirectToRoute('app_application_job_show', [
                'id' => $coverletter->getApplication()->getApplication_id()
            ]);
        }

        return $this->render('coverletter/edit.html.twig', [
            'form' => $form->createView(),
            'coverletter' => $coverletter
        ]);
    }

    #[Route('/{id}', name: 'app_coverletter_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Coverletter $coverletter,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$coverletter->getId_cover_letter(), $request->request->get('_token'))) {
            $entityManager->remove($coverletter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_application_job_edit', [
            'id' => $coverletter->getApplication()->getApplication_id()
        ]);
    }
}