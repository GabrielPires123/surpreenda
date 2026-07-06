<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'home_')]
class HomeController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('landing/index.html.twig');
    }

    #[Route('/ajuda', name: 'ajuda')]
    public function ajuda(): Response
    {
        return $this->render('ajuda/index.html.twig');
    }

    #[Route('/ajuda/central', name: 'ajuda_central')]
    public function ajudaCentral(): Response
    {
        return $this->render('ajuda/central.html.twig');
    }
}
