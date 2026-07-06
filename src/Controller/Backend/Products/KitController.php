<?php

declare(strict_types=1);

namespace App\Controller\Backend\Products;

use App\Dto\Response\KitResponse;
use App\Service\KitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/kits', name: 'api_kits_')]
class KitController extends AbstractController
{
    public function __construct(
        private KitService $kitService,
    ) {
    }

    #[Route(name: '/list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $kits = $this->kitService->listKitsDisponiveis();

        return new JsonResponse(array_map(fn($kit) => KitResponse::fromEntity($kit), $kits));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function show(string $id): JsonResponse
    {
        try {
            $kit = $this->kitService->getKitOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(KitResponse::fromEntity($kit));
    }
}
