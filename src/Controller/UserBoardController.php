<?php

namespace App\Controller;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserBoardController extends AbstractController
{
    #[Route('/user/board', name: 'app_user_dashboard')]
    public function index(): Response
    {
        return $this->render('user_board/Dashboard.html.twig', [
            'controller_name' => 'UserBoardController',
        ]);
    }

    #[Route('/user/profile', name: 'app_user_profile')]
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

        return $this->render('user_board/Profile.html.twig', [
            'controller_name' => 'UserBoardController',
            'form' => $form->createView(),
        ]);
    }
}