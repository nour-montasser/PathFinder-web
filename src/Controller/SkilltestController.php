<?php

namespace App\Controller;

use App\Entity\Job_offer;
use App\Service\AiAssistantService;
use App\Repository\SkilltestRepository;
use App\Entity\Test_result;
use App\Entity\App_user;
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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Entity\ApplicationJob;


#[Route('/skilltest')]
final class SkilltestController extends BaseController
{
    //   private PusherInterface $pusher;

    ////   public function __construct(PusherInterface $pusher)
    ///  {
    ////     $this->pusher = $pusher;
    ///  }

    #[Route('/', name: 'app_skilltest_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        $skilltests = $entityManager->createQueryBuilder()
            ->select('s')
            ->from(Skilltest::class, 's')
            ->join('s.jobOffer', 'j')
            ->where('j.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $this->render('skilltest/index.html.twig', [
            'skilltests' => $skilltests,
        ]);
    }

    #[Route('/new/{jobOfferId}', name: 'app_skilltest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $jobOfferId): Response
    {
        $jobOffer = $entityManager->getRepository(Job_offer::class)->find($jobOfferId);

        if (!$jobOffer) {
            throw $this->createNotFoundException('JobOffer not found.');
        }

        $skilltest = new Skilltest();
        $skilltest->setJobOffer($jobOffer);

        $form = $this->createForm(SkilltestType::class, $skilltest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($skilltest->getQuestions() as $question) {
                $question->setSkillTest($skilltest);
                $entityManager->persist($question);
            }

            $entityManager->persist($skilltest);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_offer_show', [
                'id_offer' => $jobOffer->getIdOffer(),
            ]);        }

        return $this->render('skilltest/new.html.twig', [
            'skilltestForm' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_skilltest_show', methods: ['GET'])]
    public function show(SkilltestRepository $repo, int $id): Response
    {
        $skilltest = $repo->findSkilltestWithQuestions($id);

        if (!$skilltest) {
            throw $this->createNotFoundException('Skilltest not found!');
        }

        return $this->render('skilltest/show.html.twig', [
            'skilltest' => $skilltest,
        ]);
    }


    #[Route('/{id}/edit/{jobOfferId}', name: 'app_skilltest_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Skilltest $skilltest, EntityManagerInterface $entityManager, int $jobOfferId): Response
    {
        $jobOffer = $entityManager->getRepository(Job_offer::class)->find($jobOfferId);

        if (!$jobOffer) {
            throw $this->createNotFoundException('JobOffer not found.');
        }

        $skilltest->setJobOffer($jobOffer); // ensure it's correctly assigned

        $form = $this->createForm(SkilltestType::class, $skilltest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($skilltest->getQuestions() as $question) {
                $question->setSkillTest($skilltest);
                $entityManager->persist($question);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_job_offer_show', [
                'id_offer' => $jobOffer->getIdOffer()
            ]);
        }

        return $this->render('skilltest/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/skilltest/{id}', name: 'app_skilltest_delete', methods: ['POST'])]
    public function delete(Skilltest $skilltest, EntityManagerInterface $em, Request $request): Response
    {
        // Get the related JobOffer before deleting the SkillTest
        $jobOffer = $skilltest->getJobOffer();

        if ($this->isCsrfTokenValid('delete' . $skilltest->getId(), $request->request->get('_token'))) {
            $em->remove($skilltest);
            $em->flush();
        }

        // Redirect to the job offer show page
        return $this->redirectToRoute('app_job_offer_show', [
            'id_offer' => $jobOffer->getIdOffer(),
        ]);
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
    public function take(
        Skilltest $skilltest,
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response
    {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();

        $testResult = $em->getRepository(Test_result::class)->findOneBy([
            'user' => $user,
            'test' => $skilltest
        ]);

        $form = null;

        // Always calculate passed/failed
        $allResults = $em->getRepository(Test_result::class)->findBy(['test' => $skilltest]);
        $passed = 0;
        $failed = 0;
        foreach ($allResults as $result) {
            if ($result->getStatus()) {
                $passed++;
            } else {
                $failed++;
            }
        }

        if ($testResult) {
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
                'total' => max(1, count($skilltest->getQuestions())),
                'percentage' => $testResult->getResult(),
                'passed' => $passed,
                'failed' => $failed,
                'passedBoolean' => $testResult->getStatus(), // for color logic
                'skilltest' => $skilltest,
                'alreadyRated' => $testResult->getRating() !== null,
                'form' => $form?->createView(),
                'testResult' => $testResult,
            ]);
        }

        $questions = $skilltest->getQuestions();
        $pagination = $paginator->paginate(
            $questions,
            $request->query->getInt('page', 1),
            1
        );

        if ($request->isMethod('POST')) {
            if (count($questions) === 0) {
                $this->addFlash('error', 'This test has no questions.');
                return $this->redirectToRoute('app_skilltest_index');
            }

            $score = 0;
            $total = count($questions);

            foreach ($questions as $question) {
                $submittedAnswer = $request->request->get('question_' . $question->getId());
                if (trim($submittedAnswer) === trim($question->getCorrectResponse())) {
                    $score++;
                }
            }

           // In the POST handling section of the take action, after calc// In the POST handling section after calculating score:
$percentage = ($score / max(1, $total)) * 100;
$status = $score >= $skilltest->getScoreRequired();

$newResult = new Test_result();
$newResult->setUser($user);
$newResult->setTest($skilltest);
$newResult->setDate(new \DateTime());
$newResult->setResult($percentage);
$newResult->setStatus($status);

$em->persist($newResult);

// Find and update the related application
$applicationRepo = $em->getRepository(ApplicationJob::class);
$application = $applicationRepo->findOneBy([
    'user' => $user,
    'jobOffer' => $skilltest->getJobOffer(),
    'status' => 'Applying-3' // Currently on step 3
]);

// In SkilltestController.php - Inside the POST handler
if ($application) {
    if (!$status) {
        $application->setStatus('Rejected');
        $application->setDateApplication(new \DateTime());
        $em->flush();
    }
}

$em->flush(); // Flush the test result if no application was found

$form = $this->createForm(TestresultType::class, $newResult);

            return $this->render('skilltest/result.html.twig', [
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'passed' => $passed,
                'failed' => $failed,
                'passedBoolean' => $status,
                'skilltest' => $skilltest,
                'alreadyRated' => false,
                'form' => $form->createView(),
                'testResult' => $newResult,
            ]);
        }

        return $this->render('skilltest/take.html.twig', [
            'skilltest' => $skilltest,
            'pagination' => $pagination,
        ]);
    }



    #[Route('/skilltest/search-advanced', name: 'app_skilltest_search_advanced', methods: ['GET'])]
    public function advancedSearch(): Response
    {
        return $this->render('skilltest/advanced_search.html.twig');
    }

    #[Route('/skilltest/search', name: 'app_skilltest_search', methods: ['GET'])]
    public function ajaxSearch(Request $request, SkilltestRepository $repo): Response
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
        $this->ensureUserSession();
$user = $this->getCurrentUser();
if (!$user) {
    throw $this->createNotFoundException('User not found.');
}

        if (!$user) {
            throw $this->createNotFoundException("User not found.");
        }

        $result = $em->getRepository(Test_result::class)->findOneBy(
            ['user' => $user, 'test' => $skilltest],
            ['date' => 'DESC']
        );

        if (!$result) {
            throw $this->createNotFoundException('Result not found.');
        }

        $html = $this->renderView('skilltest/pdf_template.html.twig', [
            'user' => $user,
            'skilltest' => $skilltest,
            'result' => $result
        ]);

        $pdfContent = $knpSnappy->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="resultat_skilltest.pdf"',
        ]);
    }

    #[Route('/ai/ask', name: 'ai_ask', methods: ['POST'])]
    public function askAi(Request $request, AiAssistantService $aiService): JsonResponse
    {
        $question = $request->request->get('question');

        if (!$question) {
            return new JsonResponse(['error' => 'No question provided.'], 400);
        }

        $answer = $aiService->askQuestion($question);

        return new JsonResponse(['response' => $answer]);
    }

    #[Route('/skilltest/{id}/statistics', name: 'app_skilltest_statistics')]
    public function statistics(Skilltest $skilltest, EntityManagerInterface $em, ChartBuilderInterface $chartBuilder): Response
    {
        $testResults = $em->getRepository(Test_result::class)->findBy(['test' => $skilltest]);
        $passed = 0;
        $failed = 0;

        foreach ($testResults as $result) {
            if ($result->getStatus()) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $total = $passed + $failed;

        $difficulty = null;
        if ($total > 0) {
            $successRate = ($passed / $total) * 100;
            if ($successRate >= 80) {
                $difficulty = 'Easy';
            } elseif ($successRate >= 50) {
                $difficulty = 'Medium';
            } else {
                $difficulty = 'Hard';
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => ['Passed', 'Failed'],
            'datasets' => [[
                'label' => 'Test Results',
                'backgroundColor' => ['#28a745', '#dc3545'],
                'data' => [$passed, $failed],
            ]],
        ]);
        $chart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
        ]);

        return $this->render('skilltest/statistics.html.twig', [
            'skilltest' => $skilltest,
            'passed' => $passed,
            'failed' => $failed,
            'total' => $total,
            'difficulty' => $difficulty,
            'chart' => $chart,
        ]);
    }
}