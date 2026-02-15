<?php

namespace App\Controller\User;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
final class UserBoardController extends AbstractController
{
    #[Route('/board', name: 'app_user_dashboard')]
    public function index(): Response
    {
        return $this->render('user/board/Dashboard.html.twig', [
            'controller_name' => 'UserBoardController',
        ]);
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user/board/Profile.html.twig', [
            'controller_name' => 'UserBoardController',
            'form' => $form->createView(),
        ]);
    }
}