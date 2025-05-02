<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Service\FeedbackMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response; // Important!!

class FeedbackController extends BaseController
{
    #[Route('/submit-feedback', name: 'feedback_submit', methods: ['POST'])]
    public function submitFeedback(
        Request $request,
        EntityManagerInterface $entityManager,
        FeedbackMailer $feedbackMailer
        
    ): JsonResponse {
        $this->ensureUserSession();
        $user = $this->getCurrentUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'You must be logged in to submit feedback.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $name = $request->request->get('name');
        $subject = $request->request->get('subject');
        $message = $request->request->get('message');

        try {
            $feedback = new Feedback();
            $feedback->setName($name);
            $feedback->setSubject($subject);
            $feedback->setMessage($message);
    
            $entityManager->persist($feedback);
            $entityManager->flush();
    
            // Send thank you email to the user
            $feedbackMailer->sendThankYouEmail($user);
    
            return new JsonResponse([
                'success' => true,
                'message' => 'Feedback submitted successfully! Thank you email sent.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
