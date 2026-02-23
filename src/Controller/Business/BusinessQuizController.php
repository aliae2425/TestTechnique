<?php

namespace App\Controller\Business;

use App\Entity\Question;
use App\Entity\QuizTemplate;
use App\Form\BusinessQuestionType;
use App\Form\QuizTemplateType;
use App\Repository\QuestionRepository;
use App\Repository\QuizTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/business/quiz', name: 'business_quiz_')]
#[IsGranted('ROLE_ENTREPRISE')]
final class BusinessQuizController extends AbstractController
{
    /**
     * Page principale de gestion des quiz et questions
     */
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        QuizTemplateRepository $quizTemplateRepo,
        QuestionRepository $questionRepo,
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        // --- Quizzes ---
        $companyQuizzes = $quizTemplateRepo->findByCompany($company);
        $platformQuizzes = $quizTemplateRepo->findPlatform();

        // --- Questions ---
        $filterLevel = $request->query->get('level');
        $filterType = $request->query->get('type');
        $filterOwner = $request->query->get('owner', 'all');

        if ($filterOwner === 'company') {
            $companyQuestions = $questionRepo->findBy(['company' => $company], ['titled' => 'ASC']);
            $platformQuestions = [];
        } else {
            $companyQuestions = $questionRepo->findForCompany($company, $filterLevel ?: null, $filterType ?: null);
            $platformQuestions = [];
            // Split into company vs platform for display
            $allQuestions = $companyQuestions;
            $companyQuestions = array_filter($allQuestions, fn($q) => $q->getCompany() !== null);
            $platformQuestions = array_filter($allQuestions, fn($q) => $q->getCompany() === null);
        }

        // --- Forms ---
        $quizForm = $this->createForm(QuizTemplateType::class, null, [
            'action' => $this->generateUrl('business_quiz_new_quiz'),
            'method' => 'POST',
            'company' => $company,
        ]);

        $questionForm = $this->createForm(BusinessQuestionType::class, null, [
            'action' => $this->generateUrl('business_quiz_new_question'),
            'method' => 'POST',
        ]);

        return $this->render('business/quiz/index.html.twig', [
            'companyQuizzes' => $companyQuizzes,
            'platformQuizzes' => $platformQuizzes,
            'companyQuestions' => array_values($companyQuestions),
            'platformQuestions' => array_values($platformQuestions),
            'quizForm' => $quizForm->createView(),
            'questionForm' => $questionForm->createView(),
            'company' => $company,
            'filterLevel' => $filterLevel ?? '',
            'filterType' => $filterType ?? '',
            'filterOwner' => $filterOwner,
            'activeTab' => $request->query->get('tab', 'quiz'),
            'statsUrl' => $this->generateUrl('business_quiz_question_stats', ['id' => 0]),
        ]);
    }

    /**
     * Créer un nouveau quiz
     */
    #[Route('/new-quiz', name: 'new_quiz', methods: ['POST'])]
    public function newQuiz(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException();
        }

        $quiz = new QuizTemplate();
        $form = $this->createForm(QuizTemplateType::class, $quiz, ['company' => $company]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quiz->setCompany($company);

            // Nettoyer la section non utilisée selon le mode
            if ($quiz->getMode() === 'Fixed') {
                foreach ($quiz->getRules()->toArray() as $rule) {
                    $quiz->removeRule($rule);
                }
            } else {
                foreach ($quiz->getQuestions()->toArray() as $q) {
                    $quiz->removeQuestion($q);
                }
            }

            $entityManager->persist($quiz);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Quiz "%s" créé avec succès.', $quiz->getTitre()));
        } else {
            $this->addFlash('error', 'Le formulaire du quiz est invalide.');
        }

        return $this->redirectToRoute('business_quiz_index', ['tab' => 'quiz']);
    }

    /**
     * Supprimer un quiz de l'entreprise
     */
    #[Route('/{id}/delete-quiz', name: 'delete_quiz', methods: ['POST'])]
    public function deleteQuiz(
        int $id,
        Request $request,
        QuizTemplateRepository $quizTemplateRepo,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_quiz_' . $id, $request->request->get('_token')))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('business_quiz_index', ['tab' => 'quiz']);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $quiz = $quizTemplateRepo->find($id);

        if (!$quiz || $quiz->getCompany() !== $company) {
            throw $this->createNotFoundException('Quiz non trouvé.');
        }

        $entityManager->remove($quiz);
        $entityManager->flush();

        $this->addFlash('success', 'Quiz supprimé.');
        return $this->redirectToRoute('business_quiz_index', ['tab' => 'quiz']);
    }

    /**
     * Créer une nouvelle question custom pour l'entreprise
     */
    #[Route('/new-question', name: 'new_question', methods: ['POST'])]
    public function newQuestion(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException();
        }

        $question = new Question();
        $form = $this->createForm(BusinessQuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setCompany($company);
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Question "%s" créée.', $question->getTitled()));
        } else {
            $this->addFlash('error', 'Le formulaire de question est invalide.');
        }

        return $this->redirectToRoute('business_quiz_index', ['tab' => 'questions']);
    }

    /**
     * Supprimer une question de l'entreprise
     */
    #[Route('/question/{id}/delete', name: 'delete_question', methods: ['POST'])]
    public function deleteQuestion(
        int $id,
        Request $request,
        QuestionRepository $questionRepo,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_question_' . $id, $request->request->get('_token')))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('business_quiz_index', ['tab' => 'questions']);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $question = $questionRepo->find($id);

        if (!$question || $question->getCompany() !== $company) {
            throw $this->createNotFoundException('Question non trouvée.');
        }

        $entityManager->remove($question);
        $entityManager->flush();

        $this->addFlash('success', 'Question supprimée.');
        return $this->redirectToRoute('business_quiz_index', ['tab' => 'questions']);
    }

    /**
     * Stats d'une question (histogramme des réponses) — JSON
     */
    #[Route('/question/{id}/stats', name: 'question_stats', methods: ['GET'])]
    public function questionStats(
        int $id,
        QuestionRepository $questionRepo,
    ): JsonResponse {
        $question = $questionRepo->find($id);

        if (!$question) {
            return new JsonResponse(['error' => 'Question non trouvée.'], 404);
        }

        $distribution = $questionRepo->getAnswerDistribution($id);
        $total = array_sum(array_column($distribution, 'count'));

        $answers = array_map(function (array $row) use ($total) {
            $count = (int) $row['count'];
            return [
                'id' => $row['id'],
                'text' => $row['text'],
                'isCorrect' => (bool) $row['is_correct'],
                'count' => $count,
                'pct' => $total > 0 ? round($count / $total * 100) : 0,
            ];
        }, $distribution);

        return new JsonResponse([
            'id' => $question->getId(),
            'titled' => $question->getTitled(),
            'level' => $question->getLevel(),
            'type' => $question->getType(),
            'description' => $question->getDescription(),
            'isCompany' => $question->getCompany() !== null,
            'answers' => $answers,
            'total' => (int) $total,
        ]);
    }
}
