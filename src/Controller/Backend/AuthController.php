<?php

declare(strict_types=1);

namespace App\Controller\Backend;

use App\Dto\Request\LoginRequest;
use App\Dto\Request\RegisterRequest;
use App\Dto\Response\AuthResponse;
use App\Entity\User;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        try {
            $dto = RegisterRequest::fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->authService->registerUser(
                $dto->nome,
                $dto->email,
                $dto->cpf,
                $dto->password,
                $dto->telefone,
                $dto->endereco
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cliente = $user->getCliente();
        if ($cliente === null) {
            return new JsonResponse(['error' => 'Cliente não foi criado para este usuário.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(AuthResponse::fromUserAndCliente($user, $cliente), Response::HTTP_CREATED);
    }

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $dto = LoginRequest::fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->authService->login($dto->email, $dto->password);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(AuthResponse::fromUser($user));
    }

    #[Route('/auth/me', name: 'auth_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        [$currentUser, $cliente] = $this->authService->getUserAndCliente($user->getId());

        return new JsonResponse(AuthResponse::fromUserAndClienteOptional($currentUser, $cliente));
    }
}
