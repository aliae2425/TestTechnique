<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\QuizSession;
use App\Entity\QuizTemplate;
use App\Entity\UserReponses;
use App\Repository\QuestionRepository;
use App\Repository\QuizSessionRepository;
use App\Repository\QuizTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class QuizController extends AbstractController
{
    private const QUIZ_LENGTH = 25;

    #[Route('/quiz', name: 'app_quiz')]
    public function index(): Response
    {
        return $this->render('quiz/index.html.twig', [
            'controller_name' => 'QuizController',
        ]);
    }

    #[Route('/quiz/start/{id}', name: 'app_quiz_start', methods: ['GET', 'POST'])]
    public function startQuiz(int $id, Request $request, QuizTemplateRepository $quizTemplateRepository, QuestionRepository $questionRepository, QuizSessionRepository $quizSessionRepository, EntityManagerInterface $entityManager): Response
    {
        $template = $quizTemplateRepository->find($id);

        if (!$template) {
            throw $this->createNotFoundException('Quiz Template not found');
        }

        if ($request->isMethod('POST')) {
            return $this->handleQuizSubmission($request, $template, $questionRepository, $quizSessionRepository, $entityManager);
        }

        // Initialize Session on Start (GET) to track duration
        $session = $this->createQuizSession($template);
        $entityManager->persist($session);
        $entityManager->flush();

        $questions = $this->getQuizQuestions($template, $questionRepository);

        $view = $template->getType() === 'Exam' ? 'quiz/Exam_render.html.twig' : 'quiz/render.html.twig';

        return $this->render($view, [
            'quiz' => $template,
            'questions' => $questions,
            'quizSession' => $session,
        ]);
    }

    private function handleQuizSubmission(Request $request, QuizTemplate $template, QuestionRepository $questionRepository, QuizSessionRepository $quizSessionRepository, EntityManagerInterface $entityManager): Response
    {
        $score = 0;
        $results = [];

        // Try to retrieve existing session or create fallback
        $sessionId = $request->request->get('session_id');
        $session = $sessionId ? $quizSessionRepository->find($sessionId) : null;

        if (!$session) {
            $session = $this->createQuizSession($template);
        }

        foreach ($request->request->all() as $key => $value) {
            if (str_starts_with($key, 'q_')) {
                $questionId = (int) substr($key, 2);
                $question = $questionRepository->find($questionId);
                
                if ($question) {
                    $result = $this->processQuestionResponse($question, $value, $session, $entityManager);
                    
                    if ($result['isCorrect']) {
                        $score++;
                    }
                    
                    $results[] = $result;
                }
            }
        }

        $session->setFinalScore($score);
        $session->setEndAt(new \DateTimeImmutable());
        $entityManager->persist($session);
        $entityManager->flush();
        
        $totalQuestions = (int) $request->request->get('total_questions', count($results));

        return $this->render('quiz/result.html.twig', [
            'quiz' => $template,
            'score' => $score,
            'total' => $totalQuestions,
            'results' => $results
        ]);
    }

    private function createQuizSession(QuizTemplate $template): QuizSession
    {
        $session = new QuizSession();
        $session->setQuizTemplate($template);
        $session->setUser($this->getUser());
        $session->setStartAt(new \DateTimeImmutable());
        
        return $session;
    }

    private function processQuestionResponse(Question $question, mixed $value, QuizSession $session, EntityManagerInterface $entityManager): array
    {
        // Ensure value is an array (checkboxes)
        $selectedAnswerIds = array_map('intval', (array) $value);
        
        // Get correct answers IDs
        $correctAnswerIds = [];
        foreach ($question->getReponses() as $answer) {
            if ($answer->isCorrect()) {
                $correctAnswerIds[] = $answer->getId();
            }
        }
        
        // Sort for comparison
        sort($selectedAnswerIds);
        sort($correctAnswerIds);
        
        $isCorrect = ($selectedAnswerIds === $correctAnswerIds);
        
        // Save UserReponses
        foreach ($selectedAnswerIds as $answerId) {
            $selectedAnswer = null;
            foreach ($question->getReponses() as $a) {
                if ($a->getId() === $answerId) {
                    $selectedAnswer = $a;
                    break;
                }
            }
            
            if ($selectedAnswer) {
                $userResponse = new UserReponses();
                $userResponse->setSession($session);
                $userResponse->setQuestion($question);
                $userResponse->setReponse($selectedAnswer);
                $userResponse->setTimeSpent(0); // TODO: Measure time per question
                
                $entityManager->persist($userResponse);
            }
        }

        return [
            'question' => $question,
            'isCorrect' => $isCorrect,
            'selectedIds' => $selectedAnswerIds,
            'correctIds' => $correctAnswerIds
        ];
    }

    private function getQuizQuestions(QuizTemplate $template, QuestionRepository $questionRepository): array
    {
        $questions = [];

        if ($template->getMode() === 'Random') {
            $questions = $questionRepository->findRandomQuestions(self::QUIZ_LENGTH);
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

        return $questions;
    }

}