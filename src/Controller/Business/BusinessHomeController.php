<?php

namespace App\Controller\Business;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BusinessHomeController extends AbstractController
{
    #[Route('/business/home', name: 'app_business_home')]
    public function index(): Response
    {
        return $this->render('business_home/index.html.twig', [
            'controller_name' => 'BusinessHomeController',
        ]);
    }
}
