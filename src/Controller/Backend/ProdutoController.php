<?php

namespace App\Controller\Backend;

use App\Entity\Produto;
use App\Repository\CategoriaRepository;
use App\Repository\Interface\ProdutoRepositoryInterface;
use App\Validator\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/produtos')]
#[IsGranted('ROLE_ADMIN')]
class ProdutoController extends AbstractController
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $repository,
        private readonly CategoriaRepository $categoriaRepository,
        private readonly EntityManagerInterface $em,
        private readonly EntityValidator $validator,
    ) {
    }

    #[Route('', name: 'api_produtos_list', methods: ['GET'])]
    public function list(): Response
    {
        $produtos = $this->repository->findAllAtivos();

        return $this->json($produtos, 200, [], ['groups' => ['produto:read']]);
    }

    #[Route('', name: 'api_produto_create', methods: ['POST'])]
    public function create(JsonRequest $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $produto = new Produto();
        $produto->setNome($data['nome'] ?? '');
        $produto->setDescricao($data['descricao'] ?? '');
        $produto->setPrecoCusto($data['precoCusto'] ?? 0.0);
        $produto->setPrecoVenda($data['precoVenda'] ?? 0.0);
        $produto->setEstoque($data['estoque'] ?? 0);
        $produto->setImagemUrl($data['imagemUrl'] ?? '');

        $categoriaId = $data['categoriaId'] ?? null;
        if ($categoriaId) {
            $categoria = $this->categoriaRepository->find($categoriaId);
            if ($categoria) {
                $produto->setCategoria($categoria);
            }
        }

        $this->validator->validate($produto);
        $this->em->persist($produto);
        $this->em->flush();

        return $this->json($produto, 201, [], ['groups' => ['produto:read']]);
    }

    #[Route('/{id}', name: 'api_produto_show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $produto = $this->repository->find($id);
        if (!$produto) {
            return $this->json(['error' => 'Produto não encontrado.'], 404);
        }

        return $this->json($produto, 200, [], ['groups' => ['produto:read']]);
    }

    #[Route('/{id}', name: 'api_produto_update', methods: ['PUT'])]
    public function update(string $id, JsonRequest $request): Response
    {
        $produto = $this->repository->find($id);
        if (!$produto) {
            return $this->json(['error' => 'Produto não encontrado.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['nome'])) {
            $produto->setNome($data['nome']);
        }
        if (isset($data['descricao'])) {
            $produto->setDescricao($data['descricao']);
        }
        if (isset($data['precoCusto'])) {
            $produto->setPrecoCusto($data['precoCusto']);
        }
        if (isset($data['precoVenda'])) {
            $produto->setPrecoVenda($data['precoVenda']);
        }
        if (isset($data['estoque'])) {
            $produto->setEstoque($data['estoque']);
        }
        if (isset($data['imagemUrl'])) {
            $produto->setImagemUrl($data['imagemUrl']);
        }
        if (isset($data['categoriaId'])) {
            $categoria = $this->categoriaRepository->find($data['categoriaId']);
            if ($categoria) {
                $produto->setCategoria($categoria);
            }
        }

        $this->validator->validate($produto);
        $this->em->flush();

        return $this->json($produto, 200, [], ['groups' => ['produto:read']]);
    }

    #[Route('/{id}', name: 'api_produto_delete', methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        $produto = $this->repository->find($id);
        if (!$produto) {
            return $this->json(['error' => 'Produto não encontrado.'], 404);
        }

        $this->em->remove($produto);
        $this->em->flush();

        return new JsonResponse(null, 204);
    }
}
