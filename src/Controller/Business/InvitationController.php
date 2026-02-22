<?php

namespace App\Controller\Business;

use App\Entity\Invitation;
use App\Form\InvitationEmailType;
use App\Form\InvitationLinkType;
use App\Repository\InvitationRepository;
use App\Repository\QuizTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/business/invitation', name: 'business_invitation_')]
#[IsGranted('ROLE_ENTREPRISE')]
final class InvitationController extends AbstractController
{
    /**
     * Fragment HTML du modal "Lancer un test" — rendu depuis base.html.twig via render(controller(...))
     */
    #[Route('/modal', name: 'modal')]
    public function modal(QuizTemplateRepository $quizTemplateRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $quizTemplates = $quizTemplateRepo->findAll();

        $emailForm = $this->createForm(InvitationEmailType::class, null, [
            'action' => $this->generateUrl('business_invitation_send_email'),
            'method' => 'POST',
        ]);

        $linkForm = $this->createForm(InvitationLinkType::class, null, [
            'action' => $this->generateUrl('business_invitation_generate_link'),
            'method' => 'POST',
        ]);

        return $this->render('entreprise/invitations/_modal.html.twig', [
            'quizTemplates' => $quizTemplates,
            'emailForm' => $emailForm->createView(),
            'linkForm' => $linkForm->createView(),
            'company' => $company,
        ]);
    }

    /**
     * Liste de toutes les invitations de l'entreprise
     */
    #[Route('/', name: 'index')]
    public function index(InvitationRepository $invitationRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        $invitations = $invitationRepo->findBy(['company' => $company], ['createdAt' => 'DESC']);

        return $this->render('entreprise/invitations/index.html.twig', [
            'invitations' => $invitations,
            'company' => $company,
        ]);
    }

    /**
     * Créer et envoyer une invitation nominative par email
     */
    #[Route('/send-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        $invitation = new Invitation();
        $form = $this->createForm(InvitationEmailType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = bin2hex(random_bytes(32));
            $invitation->setToken($token);
            $invitation->setType('email');
            $invitation->setPurpose('quiz');
            $invitation->setCompany($company);
            $invitation->setCreatedAt(new \DateTimeImmutable());
            $invitation->setExpiresAt(new \DateTimeImmutable('+7 days'));

            $entityManager->persist($invitation);
            $entityManager->flush();

            $invitationUrl = $urlGenerator->generate(
                'quiz_invitation_landing',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new TemplatedEmail())
                ->from(new Address('noreply@418.archi', '418 Academy'))
                ->to((string) $invitation->getEmail())
                ->subject('Invitation à rejoindre ' . $company->getName() . ' sur 418 Academy')
                ->htmlTemplate('emails/invitation.html.twig')
                ->context([
                    'invitation' => $invitation,
                    'company' => $company,
                    'invitationUrl' => $invitationUrl,
                ]);

            $mailer->send($email);

            $this->addFlash('success', sprintf(
                'Invitation envoyée à %s %s (%s).',
                $invitation->getPrenom(),
                $invitation->getNom(),
                $invitation->getEmail()
            ));
        } else {
            $this->addFlash('error', "Le formulaire d'invitation est invalide.");
        }

        return $this->redirectToRoute('business_invitation_index');
    }

    /**
     * Générer un lien d'invitation multi-usage (réponse JSON)
     */
    #[Route('/generate-link', name: 'generate_link', methods: ['POST'])]
    public function generateLink(
        Request $request,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            return new JsonResponse(['error' => 'Entreprise non trouvée.'], 403);
        }

        $invitation = new Invitation();
        $form = $this->createForm(InvitationLinkType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = bin2hex(random_bytes(32));
            $invitation->setToken($token);
            $invitation->setType('link');
            $invitation->setPurpose('quiz');
            $invitation->setCompany($company);
            $invitation->setCreatedAt(new \DateTimeImmutable());
            $invitation->setExpiresAt(new \DateTimeImmutable('+24 hours'));

            $entityManager->persist($invitation);
            $entityManager->flush();

            $url = $urlGenerator->generate(
                'quiz_invitation_landing',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return new JsonResponse([
                'url' => $url,
                'expiresAt' => $invitation->getExpiresAt()->format('d/m/Y à H:i'),
                'quiz' => $invitation->getQuizTemplate()?->getTitre() ?? 'Quiz non défini',
            ]);
        }

        return new JsonResponse(['error' => 'Formulaire invalide.'], 400);
    }

    /**
     * Supprimer une invitation
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        InvitationRepository $invitationRepo,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_invitation_' . $id, $request->request->get('_token')))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('business_invitation_index');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $invitation = $invitationRepo->findOneBy(['id' => $id, 'company' => $company]);

        if (!$invitation) {
            throw $this->createNotFoundException('Invitation non trouvée.');
        }

        $entityManager->remove($invitation);
        $entityManager->flush();

        $this->addFlash('success', 'Invitation supprimée.');

        return $this->redirectToRoute('business_invitation_index');
    }
}
