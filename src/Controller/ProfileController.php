<?php

namespace App\Controller;

use App\Dto\Request\EnderecoRequest;
use App\Dto\Request\TelefoneRequest;
use App\Dto\Response\ClienteResponse;
use App\Dto\Response\EnderecoResponse;
use App\Dto\Response\TelefoneResponse;
use App\Entity\Cliente;
use App\Entity\Endereco;
use App\Entity\Telefone;
use App\Entity\User;
use App\Service\ClienteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/profile', name: 'api_profile_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ClienteService $clienteService,
    ) {
    }

    private function getCliente(): ?Cliente
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->clienteService->getClienteByUserId($user->getId());
    }

    #[Route('', methods: ['GET'], name: 'show')]
    public function show(): JsonResponse
    {
        $cliente = $this->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Perfil de cliente não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();
        return new JsonResponse(ClienteResponse::fromUserAndCliente($user, $cliente));
    }

    #[Route('', methods: ['PUT'], name: 'update')]
    public function update(Request $request): JsonResponse
    {
        $cliente = $this->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Perfil de cliente não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $cliente = $this->clienteService->updateCliente($cliente, $data);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return new JsonResponse(ClienteResponse::fromUserAndCliente($currentUser, $cliente));
    }

    // ─── Enderecos ──────────────────────────────────────────────────────

    #[Route('/enderecos', methods: ['GET'], name: 'enderecos_list')]
    public function listEnderecos(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $enderecos = $this->clienteService->listEnderecosByUserId($user->getId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($e) => EnderecoResponse::fromEntity($e), $enderecos));
    }

    #[Route('/enderecos', methods: ['POST'], name: 'endereco_create')]
    public function createEndereco(Request $request): JsonResponse
    {
        try {
            $dto = EnderecoRequest::fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $endereco = $this->clienteService->createEndereco(
                $user->getId(),
                $dto->logradouro,
                $dto->numero,
                $dto->bairro,
                $dto->cidade,
                $dto->estado,
                $dto->cep,
                $dto->complemento
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(EnderecoResponse::fromEntity($endereco), Response::HTTP_CREATED);
    }

    #[Route('/enderecos/{id}', methods: ['GET'], name: 'endereco_show')]
    public function showEndereco(string $id): JsonResponse
    {
        try {
            $endereco = $this->clienteService->getEnderecoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $endereco);

        return new JsonResponse(EnderecoResponse::fromEntity($endereco));
    }

    #[Route('/enderecos/{id}', methods: ['PUT'], name: 'endereco_update')]
    public function updateEndereco(Request $request, string $id): JsonResponse
    {
        try {
            $endereco = $this->clienteService->getEnderecoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $endereco);

        $data = json_decode($request->getContent(), true);

        try {
            $endereco = $this->clienteService->updateEndereco($id, $data);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(EnderecoResponse::fromEntity($endereco));
    }

    #[Route('/enderecos/{id}', methods: ['DELETE'], name: 'endereco_delete')]
    public function deleteEndereco(string $id): JsonResponse
    {
        try {
            $endereco = $this->clienteService->getEnderecoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $endereco);

        try {
            $this->clienteService->deleteEndereco($id);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => 'Endereço excluído com sucesso.']);
    }

    // ─── Telefones ──────────────────────────────────────────────────────

    #[Route('/telefones', methods: ['GET'], name: 'telefones_list')]
    public function listTelefones(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $telefones = $this->clienteService->listTelefonesByUserId($user->getId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($t) => TelefoneResponse::fromEntity($t), $telefones));
    }

    #[Route('/telefones', methods: ['POST'], name: 'telefone_create')]
    public function createTelefone(Request $request): JsonResponse
    {
        try {
            $dto = TelefoneRequest::fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $telefone = $this->clienteService->createTelefone($user->getId(), $dto->numero);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(TelefoneResponse::fromEntity($telefone), Response::HTTP_CREATED);
    }

    #[Route('/telefones/{id}', methods: ['GET'], name: 'telefone_show')]
    public function showTelefone(string $id): JsonResponse
    {
        try {
            $telefone = $this->clienteService->getTelefoneOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $telefone);

        return new JsonResponse(TelefoneResponse::fromEntity($telefone));
    }

    #[Route('/telefones/{id}', methods: ['PUT'], name: 'telefone_update')]
    public function updateTelefone(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $telefone = $this->clienteService->getTelefoneOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $telefone);

        try {
            $telefone = $this->clienteService->updateTelefone($id, $data);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(TelefoneResponse::fromEntity($telefone));
    }

    #[Route('/telefones/{id}', methods: ['DELETE'], name: 'telefone_delete')]
    public function deleteTelefone(string $id): JsonResponse
    {
        try {
            $telefone = $this->clienteService->getTelefoneOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $cliente = $this->getCliente();
        if ($cliente === null || $telefone->getCliente()->getId() !== $cliente->getId()) {
            return new JsonResponse(['error' => 'Acesso negado.'], Response::HTTP_FORBIDDEN);
        }

        $this->clienteService->deleteTelefone($id);

        return new JsonResponse(['message' => 'Telefone excluído com sucesso.']);
    }
}
