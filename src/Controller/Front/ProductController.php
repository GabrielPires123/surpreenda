<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\CategoriaRepository;
use App\Repository\ProdutoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private ProdutoRepository   $produtoRepository,
        private CategoriaRepository $categoriaRepository,
    ) {
    }

    #[Route('/produtos', name: 'product_index')]
    public function index(Request $request): Response
    {
        $searchQuery = trim($request->query->get('q', ''));
        $categoriaId = trim($request->query->get('categoria', ''));
        $sortBy = $request->query->get('sort', 'nome_asc');

        $qb = $this->produtoRepository->createQueryBuilder('p')
            ->andWhere('p.ativo = :ativo')
            ->setParameter('ativo', true);

        if ($searchQuery !== '') {
            $qb->andWhere('p.nome LIKE :search OR p.descricao LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($categoriaId !== '') {
            $qb->andWhere('p.categoria = :categoria')
                ->setParameter('categoria', $categoriaId);
        }

        match ($sortBy) {
            'preco_asc' => $qb->addOrderBy('p.precoVenda', 'ASC'),
            'preco_desc' => $qb->addOrderBy('p.precoVenda', 'DESC'),
            'recent' => $qb->addOrderBy('p.id', 'DESC'),
            default => $qb->addOrderBy('p.nome', 'ASC'),
        };

        $produtos = $qb->getQuery()->getResult();

        $categorias = $this->categoriaRepository->findAll();

        return $this->render('product/index.html.twig', [
            'produtos' => $produtos,
            'categorias' => $categorias,
            'searchQuery' => $searchQuery,
            'selectedCategoria' => $categoriaId,
            'selectedSort' => $sortBy,
        ]);
    }

    #[Route('/categoria/{id}', name: 'product_by_category')]
    public function byCategory(string $id): Response
    {
        $categoria = $this->categoriaRepository->find($id);

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
