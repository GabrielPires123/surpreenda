<?php

namespace App\Controller;

use App\Dto\Request\CheckoutRequest;
use App\Dto\Response\AssinaturaResponse;
use App\Dto\Response\KitResponse;
use App\Dto\Response\PedidoResponse;
use App\Dto\Response\PetResponse;
use App\Entity\User;
use App\Service\AssinaturaService;
use App\Service\KitService;
use App\Service\PedidoService;
use App\Service\PetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/subscription', name: 'api_subscription_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class SubscriptionFlowController extends AbstractController
{
    public function __construct(
        private readonly KitService $kitService,
        private readonly PetService $petService,
        private readonly PedidoService $pedidoService,
        private readonly AssinaturaService $assinaturaService,
    ) {
    }

    private function getUserId(): int
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user->getId();
    }

    // ─── Step 1: List Available Kits ────────────────────────────────────

    #[Route('/kits', methods: ['GET'], name: 'kits_list')]
    public function listKits(): JsonResponse
    {
        $kits = $this->kitService->listKitsDisponiveis();

        return new JsonResponse(array_map(fn($kit) => KitResponse::fromEntity($kit), $kits));
    }

    #[Route('/kits/{id}', methods: ['GET'], name: 'kit_show')]
    public function showKit(string $id): JsonResponse
    {
        try {
            $kit = $this->kitService->getKitOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(KitResponse::fromEntity($kit));
    }

    // ─── Step 2: List User's Pets ───────────────────────────────────────

    #[Route('/pets', methods: ['GET'], name: 'pets_list')]
    public function listPets(): JsonResponse
    {
        try {
            $pets = $this->petService->getPetsByUserId($this->getUserId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($pet) => PetResponse::fromEntity($pet), $pets));
    }

    // ─── Step 3: Checkout — Create Pedido + Assinatura ──────────────────

    #[Route('/checkout', methods: ['POST'], name: 'checkout')]
    public function checkout(Request $request): JsonResponse
    {
        try {
            $dto = CheckoutRequest::fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $kit = $this->kitService->getKitOrThrow($dto->kitId);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $pet = $this->petService->getPetById($dto->petId);
        if ($pet === null) {
            return new JsonResponse(['error' => 'Pet não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $pet);

        try {
            $pedido = $this->pedidoService->checkout($this->getUserId(), $kit, $pet);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'message' => 'Assinatura criada com sucesso.',
            'pedido' => PedidoResponse::fromEntity($pedido->getPedido()),
            'assinatura' => AssinaturaResponse::fromEntity($pedido->getAssinatura()),
        ], Response::HTTP_CREATED);
    }

    // ─── View User's Subscriptions ──────────────────────────────────────

    #[Route('/my', methods: ['GET'], name: 'my_subscriptions')]
    public function mySubscriptions(): JsonResponse
    {
        try {
            $assinaturas = $this->assinaturaService->listAssinaturasByUserId($this->getUserId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($a) => AssinaturaResponse::fromEntity($a), $assinaturas));
    }

    #[Route('/my/{id}', methods: ['GET'], name: 'subscription_show')]
    public function showSubscription(string $id): JsonResponse
    {
        try {
            $assinatura = $this->assinaturaService->getAssinaturaOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $assinatura);

        return new JsonResponse(AssinaturaResponse::fromEntity($assinatura));
    }

    #[Route('/my/{id}/cancel', methods: ['POST'], name: 'subscription_cancel')]
    public function cancelSubscription(string $id): JsonResponse
    {
        try {
            $assinatura = $this->assinaturaService->getAssinaturaOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $assinatura);

        try {
            $this->assinaturaService->cancelar($assinatura);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(AssinaturaResponse::fromEntity($assinatura));
    }
}
