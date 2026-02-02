<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/formations', name: 'home_formations')]
    public function formations(): Response
    {
        return $this->render('home/formations.html.twig');
    }

    #[Route('/TestTechnique', name: 'home_testTechnique')]
    public function testTechnique(): Response
    {
        return $this->render('home/test_technique.html.twig');
    }

    #[Route('/certification', name: 'home_certification')]
    public function certification(): Response
    {
        return $this->render('home/certification.html.twig');
    }
}
