<?php

namespace App\Controller\Business;

use App\Entity\QuizTemplate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/business/quiz', name: 'business_quiz_')]
#[IsGranted('ROLE_BUSINESS')]
final class BusinessQuizController extends AbstractController
{
    /**
     * Liste des Quiz :
     * 1. "Mes Quiz" (Créés par l'entreprise)
     * 2. "Catalogue" (Quiz publics de la plateforme)
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // TODO: $myQuizzes = $repo->findBy(['company' => $company]);
        // TODO: $platformQuizzes = $repo->findBy(['isPublic' => true]); (ou owner IS NULL)

        return $this->render('business/quiz/index.html.twig', [
            // 'myQuizzes' => $myQuizzes,
            // 'platformQuizzes' => $platformQuizzes,
        ]);
    }

    /**
     * Création d'un nouveau Quiz personnalisé par l'entreprise
     */
    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $quiz = new QuizTemplate();
        // TODO: $quiz->setCompany($company);

        // $form = $this->createForm(QuizTemplateType::class, $quiz);
        // $form->handleRequest($request);
        
        // if ($form->isSubmitted() && $form->isValid()) { ... }

        return $this->render('business/quiz/new.html.twig', [
            // 'form' => $form->createView(),
        ]);
    }

    /**
     * Édition d'un Quiz existant (Vérifier que c'est bien celui de l'entreprise)
     */
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(QuizTemplate $quiz, Request $request): Response
    {
        // TODO: Security Check
        // if ($quiz->getOwner() !== $this->getUser()) { throw $this->createAccessDeniedException(); }

        return $this->render('business/quiz/edit.html.twig', [
            // 'form' => ...
        ]);
    }
    
    /**
     * Prévisualisation avant envoi
     */
    #[Route('/{id}', name: 'show')]
    public function show(QuizTemplate $quiz): Response
    {
        return $this->render('business/quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }
}
