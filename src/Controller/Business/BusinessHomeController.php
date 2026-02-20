<?php

namespace App\Controller\Business;

use App\Entity\Invitation;
use App\Form\InvitationType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/business', name: 'business_')]
#[IsGranted('ROLE_ENTREPRISE')]
final class BusinessHomeController extends AbstractController
{
    /**
     * Dashboard : Vue d'ensemble
     */
    #[Route('/', name: 'dashboard')]
    public function index(InvitationRepository $invitationRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        $invitations = $invitationRepo->findBy(['company' => $company], ['createdAt' => 'DESC']);

        return $this->render('entreprise/index.html.twig', [
            'company' => $company,
            'invitations' => $invitations,
        ]);
    }

    /**
     * Page de gestion du profil entreprise
     */
    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        $form = $this->createForm(\App\Form\CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil entreprise mis à jour avec succès !');

            return $this->redirectToRoute('business_profile');
        }

        $invitationForm = $this->createForm(InvitationType::class, null, [
            'action' => $this->generateUrl('business_invite_new'),
            'method' => 'POST',
        ]);

        return $this->render('entreprise/profile.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
            'invitationForm' => $invitationForm->createView(),
        ]);
    }

    /**
     * Gestion des Candidats / Invitations
     */
    #[Route('/candidates', name: 'candidates_list')]
    public function listCandidates(InvitationRepository $invitationRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        $invitations = $invitationRepo->findBy(['company' => $company], ['createdAt' => 'DESC']);

        return $this->render('entreprise/candidates/index.html.twig', [
            'invitations' => $invitations,
            'company' => $company,
        ]);
    }

    /**
     * Créer et envoyer une nouvelle invitation par email
     */
    #[Route('/invite/new', name: 'invite_new', methods: ['POST'])]
    public function createInvitation(
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
        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = bin2hex(random_bytes(32));
            $invitation->setToken($token);
            $invitation->setCompany($company);
            $invitation->setCreatedAt(new \DateTimeImmutable());
            $invitation->setExpiresAt(new \DateTimeImmutable('+7 days'));

            $entityManager->persist($invitation);
            $entityManager->flush();

            $invitationUrl = $urlGenerator->generate(
                'app_register_invitation',
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

        return $this->redirectToRoute('business_profile', ['tab' => 'users']);
    }

    /**
     * Modifier le rôle d'un utilisateur de l'entreprise
     */
    #[Route('/user/{id}/edit', name: 'user_edit', methods: ['POST'])]
    public function editUser(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('edit_user', $request->request->get('_token')))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('business_profile', ['tab' => 'users']);
        }
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $company = $currentUser->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException();
        }

        $user = $userRepository->find($id);

        if (!$user || $user->getCompany() !== $company) {
            throw $this->createNotFoundException('Utilisateur non trouvé dans cette entreprise.');
        }

        if ($company->getOwner() && $company->getOwner()->getId() === $user->getId()) {
            $this->addFlash('error', 'Impossible de modifier le rôle du propriétaire.');
            return $this->redirectToRoute('business_profile', ['tab' => 'users']);
        }

        $newRole = $request->request->get('role');
        $allowedRoles = ['ROLE_ENTREPRISE_USER', 'ROLE_ENTREPRISE_ADMIN'];

        if (!in_array($newRole, $allowedRoles, true)) {
            $this->addFlash('error', 'Rôle invalide.');
            return $this->redirectToRoute('business_profile', ['tab' => 'users']);
        }

        $user->setRoles(['ROLE_ENTREPRISE', $newRole]);
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Le rôle de %s a été mis à jour.',
            $user->getUsername() ?? $user->getEmail()
        ));

        return $this->redirectToRoute('business_profile', ['tab' => 'users']);
    }

    /**
     * Détail d'un résultat candidat
     */
    #[Route('/candidates/{id}/detail', name: 'candidate_detail')]
    public function candidateDetail(int $id, InvitationRepository $invitationRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $invitation = $invitationRepo->findOneBy(['id' => $id, 'company' => $company]);

        if (!$invitation) {
            throw $this->createNotFoundException('Invitation non trouvée.');
        }

        return $this->render('entreprise/candidates/detail.html.twig', [
            'invitation' => $invitation,
        ]);
    }
}
