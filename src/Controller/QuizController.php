<?php

namespace App\Controller;

use App\Entity\QuizSession;
use App\Entity\UserReponses;
use App\Repository\QuestionRepository;
use App\Repository\QuizTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'app_quiz')]
    public function index(): Response
    {
        return $this->render('quiz/index.html.twig', [
            'controller_name' => 'QuizController',
        ]);
    }

    #[Route('/quiz/start/{id}', name: 'app_quiz_start', methods: ['GET', 'POST'])]
    public function startQuiz(int $id, Request $request, QuizTemplateRepository $quizTemplateRepository, QuestionRepository $questionRepository, EntityManagerInterface $entityManager): Response
    {
        $template = $quizTemplateRepository->find($id);

        if (!$template) {
            throw $this->createNotFoundException('Quiz Template not found');
        }

        if ($request->isMethod('POST')) {
            $score = 0;
            $answeredQuestions = 0;
            $results = [];

            // Create Quiz Session
            $session = new QuizSession();
            $session->setQuizTemplate($template);
            $session->setUser($this->getUser());
            $session->setStartAt(new \DateTimeImmutable()); // Should ideally be passed from form
            // $session->setInvitation(...) // If we had an invitation logic here
            
            // Loop through all post parameters
            foreach ($request->request->all() as $key => $value) {
                // Check if the key starts with 'q_' (format: q_{question_id})
                if (str_starts_with($key, 'q_')) {
                    $questionId = (int) substr($key, 2);
                    $question = $questionRepository->find($questionId);
                    
                    if ($question) {
                        $answeredQuestions++;
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
                        if ($isCorrect) {
                            $score++;
                        }
                        
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

                        // Also save "incorrect" or "missed" if we wanted to track everything, 
                        // but usually we track what the user selected. 
                        // If the user selected nothing, we might want to record that too?
                        // Current logic only records selected answers.

                        $results[] = [
                            'question' => $question,
                            'isCorrect' => $isCorrect,
                            'selectedIds' => $selectedAnswerIds,
                            'correctIds' => $correctAnswerIds
                        ];
                    }
                }
            }

            $session->setFinalScore($score);
            $entityManager->persist($session);
            $entityManager->flush();
            
            return $this->render('quiz/result.html.twig', [
                'quiz' => $template,
                'score' => $score,
                'total' => count($results), // Count of questions processed
                'results' => $results
            ]);
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