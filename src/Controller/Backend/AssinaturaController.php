<?php

declare(strict_types=1);

namespace App\Controller\Backend;

use App\Dto\Response\AssinaturaResponse;
use App\Dto\Response\KitResponse;
use App\Dto\Response\PetResponse;
use App\Entity\User;
use App\Service\AssinaturaService;
use App\Service\KitService;
use App\Service\PetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/assinatura', name: 'api_assinatura_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class AssinaturaController extends AbstractController
{
    public function __construct(
        private KitService        $kitService,
        private PetService        $petService,
        private AssinaturaService $assinaturaService,
    )
    {
    }

    private function getUserId(): string
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user->getId();
    }

    #[Route('/pets', methods: ['GET'], name: 'pets')]
    public function listPets(): JsonResponse
    {
        try {
            $pets = $this->petService->getPetsByUserId($this->getUserId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($pet) => PetResponse::fromEntity($pet), $pets));
    }

    #[Route('/kits', methods: ['GET'], name: 'kits')]
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

    #[Route('/simulate', methods: ['POST'], name: 'simulate')]
    public function simulate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['pet_id']) || empty($data['kit_id'])) {
            return new JsonResponse(['error' => 'Pet ID e Kit ID são obrigatórios.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $pet = $this->petService->getPetById($data['pet_id']);
            if ($pet === null) {
                return new JsonResponse(['error' => 'Pet não encontrado.'], Response::HTTP_NOT_FOUND);
            }

            $kit = $this->kitService->getKitOrThrow($data['kit_id']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $pet);

        $simulation = $this->assinaturaService->simulateSubscription($pet, $kit);

        return new JsonResponse($simulation);
    }

    #[Route('/create', methods: ['POST'], name: 'create')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['pet_id']) || empty($data['kit_id'])) {
            return new JsonResponse(['error' => 'Pet ID e Kit ID são obrigatórios.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $pet = $this->petService->getPetById($data['pet_id']);
            if ($pet === null) {
                return new JsonResponse(['error' => 'Pet não encontrado.'], Response::HTTP_NOT_FOUND);
            }

            $kit = $this->kitService->getKitOrThrow($data['kit_id']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $pet);

        $assinatura = $this->assinaturaService->createSubscription($this->getUserId(), $pet, $kit);

        return new JsonResponse(AssinaturaResponse::fromEntity($assinatura), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'], name: 'show')]
    public function show(string $id): JsonResponse
    {
        try {
            $assinatura = $this->assinaturaService->getSubscriptionOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $assinatura);

        return new JsonResponse(AssinaturaResponse::fromEntity($assinatura));
    }

    #[Route('/my', methods: ['GET'], name: 'my')]
    public function mySubscriptions(): JsonResponse
    {
        $assinaturas = $this->assinaturaService->getUserSubscriptions($this->getUserId());

        return new JsonResponse(array_map(fn($a) => AssinaturaResponse::fromEntity($a), $assinaturas));
    }

    #[Route('/{id}/cancel', methods: ['POST'], name: 'cancel')]
    public function cancel(string $id): JsonResponse
    {
        try {
            $assinatura = $this->assinaturaService->getSubscriptionOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $assinatura);

        try {
            $this->assinaturaService->cancelSubscription($assinatura);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => 'Assinatura cancelada com sucesso.']);
    }

    #[Route('/{id}/pause', methods: ['POST'], name: 'pause')]
    public function pause(string $id): JsonResponse
    {
        try {
            $assinatura = $this->assinaturaService->getSubscriptionOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $assinatura);

        try {
            $this->assinaturaService->pauseSubscription($assinatura);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => 'Assinatura pausada com sucesso.']);
    }
}
