<?php

declare(strict_types=1);

namespace App\Controller\Backend;

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
        private ClienteService $clienteService,
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

        if (isset($data['nome'])) {
            $cliente->setNome($data['nome']);
        }

        $this->clienteService->saveCliente($cliente);

        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse(ClienteResponse::fromUserAndCliente($user, $cliente));
    }

    #[Route('/telefone', methods: ['POST'], name: 'telefone_add')]
    public function addTelefone(Request $request): JsonResponse
    {
        $cliente = $this->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Perfil de cliente não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $dto = TelefoneRequest::fromRequest($request);
            $telefone = $this->clienteService->addTelefone($cliente, $dto->ddd, $dto->numero, $dto->tipo);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(TelefoneResponse::fromEntity($telefone), Response::HTTP_CREATED);
    }

    #[Route('/telefone/{telefoneId}', methods: ['DELETE'], name: 'telefone_delete')]
    public function deleteTelefone(int $telefoneId): JsonResponse
    {
        $cliente = $this->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Perfil de cliente não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $telefone = $this->clienteService->getTelefoneById($telefoneId);
        if ($telefone === null || $telefone->getCliente()->getId() !== $cliente->getId()) {
            return new JsonResponse(['error' => 'Telefone não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $this->clienteService->deleteTelefone($telefone);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/endereco', methods: ['POST'], name: 'endereco_add')]
    public function addEndereco(Request $request): JsonResponse
    {
        $cliente = $this->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Perfil de cliente não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $dto = EnderecoRequest::fromRequest($request);
            $endereco = $this->clienteService->addEndereco(
                $cliente,
                $dto->cep,
                $dto->logradouro,
                $dto->numero,
                $dto->complemento,
                $dto->bairro,
                $dto->cidade,
                $dto->estado,
                $dto->tipo
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(EnderecoResponse::fromEntity($endereco), Response::HTTP_CREATED);
    }

    #[Route('/endereco/{enderecoId}', methods: ['DELETE'], name: 'endereco_delete')]
    public function deleteEndereco(int $enderecoId): JsonResponse
    {
        $cliente = $this->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Perfil de cliente não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $endereco = $this->clienteService->getEnderecoById($enderecoId);
        if ($endereco === null || $endereco->getCliente()->getId() !== $cliente->getId()) {
            return new JsonResponse(['error' => 'Endereço não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $this->clienteService->deleteEndereco($endereco);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
