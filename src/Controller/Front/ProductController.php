<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\CategoriaRepository;
use App\Repository\ProdutoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProdutoRepository $produtoRepository,
        private readonly CategoriaRepository $categoriaRepository,
    ) {
    }

    #[Route('/produtos', name: 'product_index')]
    public function index(): Response
    {
        $produtos = $this->produtoRepository->findBy(['ativo' => true], ['nome' => 'ASC']);

        return $this->render('product/index.html.twig', [
            'produtos' => $produtos,
        ]);
    }

    #[Route('/categoria/{slug}', name: 'product_by_category')]
    public function byCategory(string $slug): Response
    {
        $categoria = $this->categoriaRepository->findBySlug($slug);

        if ($categoria === null) {
            throw $this->createNotFoundException('Categoria não encontrada.');
        }

        $produtos = $this->produtoRepository->findBy(['categoria' => $categoria, 'ativo' => true], ['nome' => 'ASC']);

        return $this->render('product/index.html.twig', [
            'produtos' => $produtos,
            'categoria' => $categoria,
        ]);
    }

    #[Route('/produto/{id}', name: 'product_show')]
    public function show(string $id): Response
    {
        $produto = $this->produtoRepository->find($id);

        if ($produto === null || !$produto->isAtivo()) {
            throw $this->createNotFoundException('Produto não encontrado.');
        }

        return $this->render('product/show.html.twig', [
            'produto' => $produto,
        ]);
    }
}
