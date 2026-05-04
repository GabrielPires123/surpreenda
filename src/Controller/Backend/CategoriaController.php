<?php

namespace App\Controller\Backend;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use App\Repository\Interface\CategoriaRepositoryInterface;
use App\Validator\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/categorias')]
#[IsGranted('ROLE_ADMIN')]
class CategoriaController extends AbstractController
{
    public function __construct(
        private readonly CategoriaRepositoryInterface $repository,
        private readonly EntityManagerInterface $em,
        private readonly EntityValidator $validator,
    ) {
    }

    #[Route('', name: 'api_categorias_list', methods: ['GET'])]
    public function list(): Response
    {
        $categorias = $this->repository->findAllAtivas();

        return $this->json($categorias, 200, [], ['groups' => ['categoria:read']]);
    }

    #[Route('', name: 'api_categoria_create', methods: ['POST'])]
    public function create(JsonRequest $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $categoria = new Categoria();
        $categoria->setNome($data['nome'] ?? '');
        $categoria->setDescricao($data['descricao'] ?? '');
        $categoria->setIconeUrl($data['iconeUrl'] ?? '');
        $categoria->setOrdem($data['ordem'] ?? 0);

        $this->validator->validate($categoria);
        $this->em->persist($categoria);
        $this->em->flush();

        return $this->json($categoria, 201, [], ['groups' => ['categoria:read']]);
    }

    #[Route('/{id}', name: 'api_categoria_show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $categoria = $this->repository->findBySlug($id) ?? $this->repository->find($id);
        if (!$categoria) {
            return $this->json(['error' => 'Categoria não encontrada.'], 404);
        }

        return $this->json($categoria, 200, [], ['groups' => ['categoria:read']]);
    }

    #[Route('/{id}', name: 'api_categoria_update', methods: ['PUT'])]
    public function update(string $id, JsonRequest $request): Response
    {
        $categoria = $this->repository->find($id);
        if (!$categoria) {
            return $this->json(['error' => 'Categoria não encontrada.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['nome'])) {
            $categoria->setNome($data['nome']);
        }
        if (isset($data['descricao'])) {
            $categoria->setDescricao($data['descricao']);
        }
        if (isset($data['iconeUrl'])) {
            $categoria->setIconeUrl($data['iconeUrl']);
        }
        if (isset($data['ordem'])) {
            $categoria->setOrdem($data['ordem']);
        }

        $this->validator->validate($categoria);
        $this->em->flush();

        return $this->json($categoria, 200, [], ['groups' => ['categoria:read']]);
    }

    #[Route('/{id}', name: 'api_categoria_delete', methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        $categoria = $this->repository->find($id);
        if (!$categoria) {
            return $this->json(['error' => 'Categoria não encontrada.'], 404);
        }

        $this->em->remove($categoria);
        $this->em->flush();

        return new JsonResponse(null, 204);
    }
}
