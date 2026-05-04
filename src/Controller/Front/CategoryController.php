<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoriaRepository $categoriaRepository,
    ) {
    }

    #[Route('/categorias', name: 'category_index')]
    public function index(): Response
    {
        $categorias = $this->categoriaRepository->findAllAtivas();

        return $this->render('category/index.html.twig', [
            'categorias' => $categorias,
        ]);
    }
}
