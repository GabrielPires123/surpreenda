<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tarefa', name: 'tarefa_')]
class TarefaController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('tarefa/index.html.twig');
    }

    #[Route('/editar', name: 'editar')]
    public function editar(): Response
    {
        return $this->render('tarefa/editar.html.twig');
    }

    #[Route('/excluir', name: 'excluir')]
    public function excluir(): Response
    {
        return $this->render('tarefa/excluir.html.twig');
    }
}
