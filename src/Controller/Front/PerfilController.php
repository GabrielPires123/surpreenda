<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/perfil', name: 'perfil_')]
class PerfilController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('perfil/index.html.twig');
    }

    #[Route('/editar', name: 'editar')]
    public function editar(): Response
    {
        return $this->render('perfil/editar.html.twig');
    }
}
