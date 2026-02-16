<?php

namespace App\Controller\Business;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/business', name: 'business_')]
final class BusinessHomeController extends AbstractController
{
    /**
     * Dashboard : Vue d'ensemble
     * - Stats (Invitations envoyées, En attente, Terminées)
     * - Raccourcis (Créer invitation, Créer Quiz)
     */
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        
        if (!$company) {
            throw $this->createAccessDeniedException('Vous devez être rattaché à une entreprise.');
        }

        // TODO: $stats = $invitationRepo->getStatsForCompany($company);
        
        return $this->render('entreprise/index.html.twig', [
            'company' => $company,
            // 'stats' => $stats,
        ]);
    }

    /**
     * Page de gestion du profil entreprise
     * - Informations de l'entreprise (Nom, Secteur, Taille, etc.)
     * - Gestion des utilisateurs rattachés à l'entreprise (Ajouter/Supprimer)
     */
    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
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

        return $this->render('entreprise/profile.html.twig', [
            'company' => $company,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Gestion des Candidats / Invitations
     * Lister l'historique des envois avec statut (Envoyé, Ouvert, Terminé + Score)
     */
    #[Route('/candidates', name: 'candidates_list')]
    public function listCandidates(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // TODO: $invitations = $invitationRepo->findBy(['company' => $company], ['createdAt' => 'DESC']);

        return $this->render('entreprise/candidates/index.html.twig', [
            // 'invitations' => $invitations
        ]);
    }

    /**
     * Créer et envoyer une nouvelle invitation (Email + Choix du Quiz)
     */
    #[Route('/invite/new', name: 'invite_new')]
    public function createInvitation(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // TODO: Formulaire avec : 
        // - Email candidat
        // - Nom candidat
        // - Choix du Quiz (Liste Mes Quiz [Company] + Catalogue)
        
        // if submitted:
        // 1. Créer entité Invitation
        // 2. $invitation->setCompany($company);
        // 3. Générer Token unique
        // 4. Envoyer Email via MailerService
        
        return $this->render('entreprise/candidates/invite.html.twig', [
            // 'form' => $form
        ]);
    }

    /**
     * Détail d'un résultat candidat
     */
    #[Route('/candidates/{id}/detail', name: 'candidate_detail')]
    public function candidateDetail(int $id): Response
    {
        // TODO: Récupérer l'invitation et la session associée
        // Afficher les réponses spécifiques (Similaire à la vue Admin mais contexte Entreprise)
        
        return $this->render('entreprise/candidates/detail.html.twig', [
            // 'invitation' => $invitation
        ]);
    }
}
