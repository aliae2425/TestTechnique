<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use App\Repository\QuizTemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'app_quiz')]
    public function index(): Response
    {
        return $this->render('quiz/index.html.twig', [
            'controller_name' => 'QuizController',
        ]);
    }

    #[Route('/quiz/start/{id}', name: 'app_quiz_start')]
    public function startQuiz(int $id, QuizTemplateRepository $quizTemplateRepository, QuestionRepository $questionRepository): Response
    {
        $template = $quizTemplateRepository->find($id);

        if (!$template) {
            throw $this->createNotFoundException('Quiz Template not found');
        }

        $questions = [];

        if ($template->getMode() === 'Random') {
            $questions = $questionRepository->findRandomQuestions(25);
        } elseif ($template->getMode() === 'Balanced') {
            foreach ($template->getRules() as $rule) {
                $ruleQuestions = $questionRepository->findByRule(
                    $rule->getTheme(),
                    $rule->getLevel(),
                    $rule->getQuantity()
                );
                $questions = array_merge($questions, $ruleQuestions);
            }
            shuffle($questions);
        } elseif ($template->getMode() === 'Fixed') {
            $questions = $template->getQuestions()->toArray();
        }

        return $this->render('quiz/render.html.twig', [
            'quiz' => $template,
            'questions' => $questions,
        ]);
    }

}