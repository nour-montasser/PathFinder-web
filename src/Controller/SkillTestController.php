<?php

namespace App\Controller;
use App\Repository\SkilltestRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Test_result;
use App\Entity\AppUser;
use App\Entity\Skilltest;
use App\Entity\Questions;
use App\Form\SkilltestType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Base;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/skilltest')]
final class SkilltestController extends BaseController
{
    #[Route('/', name: 'app_skilltest_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $skilltests = $entityManager->getRepository(Skilltest::class)->findAll();

        return $this->render('skilltest/index.html.twig', [
            'skilltests' => $skilltests,
        ]);
    }

    #[Route('/new', name: 'app_skilltest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $skilltest = new Skilltest();

        $form = $this->createForm(SkilltestType::class, $skilltest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the Skilltest and its questions
            foreach ($skilltest->getQuestions() as $question) {
                $question->setSkillTest($skilltest);
                $entityManager->persist($question);
            }

            $entityManager->persist($skilltest);
            $entityManager->flush();

            return $this->redirectToRoute('app_skilltest_index');
        }

        return $this->render('skilltest/new.html.twig', [
            'skilltestForm' => $form->createView()
        ]);
    }


    #[Route('/{id}', name: 'app_skilltest_show')]
    public function show(SkilltestRepository $repo, $id): Response
    {
        $skilltest = $repo->findSkilltestWithQuestions($id);

        if (!$skilltest) {
            throw $this->createNotFoundException('Skilltest not found!');
        }

        return $this->render('skilltest/show.html.twig', [
            'skilltest' => $skilltest,
        ]);
    }



    #[Route('/skilltest/{id}/edit', name: 'app_skilltest_edit')]
    public function edit(Request $request, Skilltest $skilltest, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SkilltestType::class, $skilltest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // The form handles adding/removing/updating Questions via the CollectionType
            $em->flush();

            $this->addFlash('success', '✅ SkillTest updated successfully!');
            return $this->redirectToRoute('app_skilltest_show', ['id' => $skilltest->getId()]);
        }

        return $this->render('skilltest/edit.html.twig', [
            'form' => $form->createView(),
            'skilltest' => $skilltest,
        ]);
    }


    #[Route('/skilltest/{id}', name: 'app_skilltest_delete', methods: ['POST'])]
    public function delete(Skilltest $skilltest, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $skilltest->getId(), $request->request->get('_token'))) {
            $em->remove($skilltest);
            $em->flush();
        }
        return $this->redirectToRoute('app_skilltest_index');
    }
    #[Route('/questions/{id}', name: 'app_questions_delete', methods: ['POST'])]
    public function deleteQuestion($id, EntityManagerInterface $em, Request $request): Response
    {
        $question = $em->getRepository(Questions::class)->find($id);

        if (!$question) {
            throw $this->createNotFoundException('Question not found!');
        }

        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $em->remove($question);
            $em->flush();
        }

        return $this->redirectToRoute('app_skilltest_show', [
            'id' => $question->getSkillTest()->getId()
        ]);
    }
    #[Route('/skilltest/{id}/take', name: 'app_skilltest_take')]
    public function take(Skilltest $skilltest, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $em->getRepository(\App\Entity\App_user::class)->find(2);


        if ($request->isMethod('POST')) {
            $score = 0;
            $total = count($skilltest->getQuestions());

            foreach ($skilltest->getQuestions() as $question) {
                $submittedAnswer = $request->request->get('question_' . $question->getId());
                if (trim($submittedAnswer) === trim($question->getCorrectResponse())) {
                    $score++;
                }
            }

            $percentage = ($score / max(1, $total)) * 100;
            $status = $score >= $skilltest->getScoreRequired();

            $testResult = new Test_result();
            $testResult->setUser($user);
            $testResult->setTest($skilltest);
            $testResult->setDate(new \DateTime());
            $testResult->setResult($percentage);
            $testResult->setStatus($status);

            $em->persist($testResult);
            $em->flush();

            return $this->render('skilltest/result.html.twig', [
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'passed' => $status,
                'skilltest' => $skilltest
            ]);
        }

        return $this->render('skilltest/take.html.twig', [
            'skilltest' => $skilltest,
        ]);
    }



}