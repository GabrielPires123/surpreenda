<?php

declare(strict_types=1);

namespace App\Controller\Backend;

use App\Dto\Response\PedidoResponse;
use App\Entity\User;
use App\Service\PedidoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pedidos', name: 'api_pedidos_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class PedidoController extends AbstractController
{
    public function __construct(
        private PedidoService $pedidoService,
    ) {
    }

    #[Route(name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $pedidos = $this->pedidoService->listPedidosByUserId($user->getId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($p) => PedidoResponse::fromEntity($p), $pedidos));
    }

    #[Route('/{id}', methods: ['GET'], name: 'show')]
    public function show(string $id): JsonResponse
    {
        try {
            $pedido = $this->pedidoService->getPedidoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $pedido);

        return new JsonResponse(PedidoResponse::fromEntity($pedido));
    }

    #[Route('/{id}/cancel', methods: ['POST'], name: 'cancel')]
    public function cancel(string $id): JsonResponse
    {
        try {
            $pedido = $this->pedidoService->getPedidoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $pedido);

        try {
            $this->pedidoService->cancelar($pedido);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(PedidoResponse::fromEntity($pedido));
    }

    #[Route('/{id}/track', methods: ['GET'], name: 'track')]
    public function track(string $id): JsonResponse
    {
        try {
            $pedido = $this->pedidoService->getPedidoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $pedido);

        return new JsonResponse(['status' => $pedido->getStatus()->value]);
    }
}
