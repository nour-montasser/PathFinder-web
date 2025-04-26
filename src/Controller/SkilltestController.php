<?php

namespace App\Controller;


use App\Service\AiAssistantService;
use App\Repository\SkilltestRepository;
use App\Entity\Test_result;
use App\Entity\AppUser;
use App\Entity\Skilltest;
use App\Entity\Questions;
use App\Form\SkilltestType;
use App\Form\TestresultType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/skilltest')]
final class SkilltestController extends AbstractController
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
    public function take(Skilltest $skilltest, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(\App\Entity\AppUser::class)->find(3);

        $testResult = $em->getRepository(Test_result::class)->findOneBy([
            'user' => $user,
            'test' => $skilltest
        ]);

        $form = null;

        if ($testResult) {
            // 👇 Allow rating if not done yet
            if ($testResult->getRating() === null) {
                $form = $this->createForm(TestresultType::class, $testResult);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $em->flush();
                    $this->addFlash('success', 'Thanks for rating!');
                    return $this->redirectToRoute('app_skilltest_take', ['id' => $skilltest->getId()]);
                }
            }

            return $this->render('skilltest/result.html.twig', [
                'score' => $testResult->getResult(),
                'total' => count($skilltest->getQuestions()),
                'percentage' => $testResult->getResult(),
                'passed' => $testResult->getStatus(),
                'skilltest' => $skilltest,
                'alreadyRated' => $testResult->getRating() !== null,
                'form' => $form?->createView()
            ]);
        }

        // ✨ If POST, handle test submission
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

            // Form will be shown for rating
            $form = $this->createForm(TestresultType::class, $testResult);

            return $this->render('skilltest/result.html.twig', [
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'passed' => $status,
                'skilltest' => $skilltest,
                'alreadyRated' => false,
                'form' => $form->createView()
            ]);
        }

        return $this->render('skilltest/take.html.twig', [
            'skilltest' => $skilltest,
        ]);
    }



    #[Route('/skilltest/search-advanced', name: 'app_skilltest_search_advanced', methods: ['GET'])]
    public function advancedSearch(): Response
    {
        return $this->render('skilltest/advanced_search.html.twig');
    }
    #[Route('/skilltest/search', name: 'app_skilltest_search', methods: ['GET'])]

    public function ajaxSearch(Request $request, SkillTestRepository $repo): Response
    {
        $title = trim($request->query->get('title', ''));
        $duration = trim($request->query->get('duration', ''));
        $score = trim($request->query->get('score', ''));

        $qb = $repo->createQueryBuilder('s');

        if ($title !== '') {
            $qb->andWhere('s.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        if ($duration !== '') {
            $qb->andWhere('s.duration <= :duration')
                ->setParameter('duration', $duration);
        }

        if ($score !== '') {
            $qb->andWhere('s.scoreRequired >= :score')
                ->setParameter('score', $score);
        }

        $results = $qb->getQuery()->getResult();

        return $this->render('skilltest/_search_results.html.twig', [
            'skilltests' => $results
        ]);
    }


    #[Route('/skilltest/{id}/pdf', name: 'app_skilltest_pdf')]
    public function downloadPdf(Skilltest $skilltest, EntityManagerInterface $em, Pdf $knpSnappy): Response
    {
        // 🔧 Simulated logged-in user (replace with real logic later)
        $user = $em->getRepository(AppUser::class)->find(3);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur non trouvé.");
        }

        // ✅ Fetch latest test result for this user & test
        $result = $em->getRepository(Test_result::class)->findOneBy(
            ['user' => $user, 'test' => $skilltest],
            ['date' => 'DESC']
        );

        // ❌ If no result found, don't proceed
        if (!$result) {
            throw $this->createNotFoundException('Résultat non trouvé pour ce test.');
        }

        // 🧾 Render the HTML version of the PDF
        $html = $this->renderView('skilltest/pdf_template.html.twig', [
            'user' => $user,
            'skilltest' => $skilltest,
            'result' => $result
        ]);

        // 📄 Generate PDF content from HTML
        $pdfContent = $knpSnappy->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="resultat_skilltest.pdf"',
        ]);
    }
    #[Route('/ai/ask', name: 'ai_ask', methods: ['POST'])]
    public function askAi(Request $request, AiAssistantService $aiService): JsonResponse
    {
        $question = $request->request->get('question'); // 🔥 FIXED!

        if (!$question) {
            return new JsonResponse(['error' => 'No question provided.'], 400);
        }

        $answer = $aiService->askQuestion($question);

        return new JsonResponse(['response' => $answer]);
    }










}
