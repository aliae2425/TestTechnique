<?php

namespace App\Controller;

use App\Entity\QuizSession;
use App\Repository\InvitationRepository;
use App\Repository\QuizSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz/invitation', name: 'quiz_invitation_')]
final class QuizInvitationController extends AbstractController
{
    /**
     * Page d'accueil d'une invitation quiz
     */
    #[Route('/{token}', name: 'landing', methods: ['GET'])]
    public function landing(
        string $token,
        InvitationRepository $invitationRepo,
    ): Response {
        $invitation = $invitationRepo->findOneBy(['token' => $token]);

        if (!$invitation || $invitation->getPurpose() !== 'quiz') {
            $this->addFlash('error', "Ce lien d'invitation est invalide.");
            return $this->redirectToRoute('app_login');
        }

        if ($invitation->isExpired()) {
            return $this->render('quiz/invitation/landing.html.twig', [
                'invitation' => $invitation,
                'expired' => true,
            ]);
        }

        return $this->render('quiz/invitation/landing.html.twig', [
            'invitation' => $invitation,
            'expired' => false,
        ]);
    }

    /**
     * Démarre le quiz guest depuis la landing page
     */
    #[Route('/{token}/start', name: 'start', methods: ['POST'])]
    public function start(
        string $token,
        Request $request,
        InvitationRepository $invitationRepo,
        EntityManagerInterface $entityManager,
    ): Response {
        $invitation = $invitationRepo->findOneBy(['token' => $token]);

        if (!$invitation || $invitation->getPurpose() !== 'quiz' || $invitation->isExpired()) {
            $this->addFlash('error', "Ce lien d'invitation est invalide ou a expiré.");
            return $this->redirectToRoute('app_login');
        }

        $quizTemplate = $invitation->getQuizTemplate();

        if (!$quizTemplate) {
            $this->addFlash('error', 'Aucun quiz associé à cette invitation.');
            return $this->redirectToRoute('quiz_invitation_landing', ['token' => $token]);
        }

        $prenom = trim((string) $request->request->get('prenom', $invitation->getPrenom() ?? ''));
        $nom = trim((string) $request->request->get('nom', $invitation->getNom() ?? ''));
        $participantName = trim($prenom . ' ' . $nom) ?: 'Anonyme';

        $session = new QuizSession();
        $session->setQuizTemplate($quizTemplate);
        $session->setUser(null);
        $session->setParticipantName($participantName);
        $session->setStartAt(new \DateTimeImmutable());
        $session->setInvitation($invitation);

        $entityManager->persist($session);
        $entityManager->flush();

        $request->getSession()->set('guest_quiz_session_id', $session->getId());

        return $this->redirectToRoute('app_quiz_start', ['id' => $quizTemplate->getId()]);
    }
}
