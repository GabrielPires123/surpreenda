<?php

declare(strict_types=1);

namespace App\Controller\Backend;

use App\Dto\Request\PetRequest;
use App\Dto\Response\PetResponse;
use App\Entity\User;
use App\Service\PetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pets', name: 'api_pets_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class PetController extends AbstractController
{
    public function __construct(
        private PetService $petService,
    ) {
    }

    #[Route(name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $pets = $this->petService->getPetsByUserId((int)$user->getId());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(array_map(fn($pet) => PetResponse::fromEntity($pet), $pets));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $pet = $this->petService->getPetById($id);
        if ($pet === null) {
            return new JsonResponse(['error' => 'Pet não encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $pet);

        return new JsonResponse(PetResponse::fromEntity($pet));
    }

    #[Route(methods: ['POST'], name: 'create')]
    public function create(Request $request): JsonResponse
    {
        try {
            $dto = PetRequest::fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();
        $pet = $this->petService->registerPetByUser($user->getId(), $dto);

        return new JsonResponse(PetResponse::fromEntity($pet), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update')]
    public function update(string $id, Request $request): JsonResponse
    {
        try {
            $pet = $this->petService->getPetById($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $pet);

        try {
            $updatedPet = $this->petService->updatePet($pet, $request);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(PetResponse::fromEntity($updatedPet));
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete')]
    public function delete(string $id): JsonResponse
    {
        try {
            $pet = $this->petService->getPetById($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $pet);

        $this->petService->deletePet($pet);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
