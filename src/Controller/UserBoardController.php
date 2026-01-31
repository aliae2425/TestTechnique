<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function profile(): Response
    {
        return $this->render('user_board/Profile.html.twig', [
            'controller_name' => 'UserBoardController',
        ]);
    }
}