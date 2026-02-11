<?php

namespace App\Controller\Admin;

use App\Entity\QuizSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ajax')]
#[IsGranted('ROLE_ADMIN')]
class QuizHistoryAjaxController extends AbstractController
{
    #[Route('/quiz-session/{id}', name: 'admin_ajax_quiz_session_details', methods: ['GET'])]
    public function details(QuizSession $quizSession): Response
    {
        return $this->render('admin/user/_quiz_details.html.twig', [
            'session' => $quizSession,
        ]);
    }
}
