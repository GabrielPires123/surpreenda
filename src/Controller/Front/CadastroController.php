<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\User;
use App\Enum\PetType;
use App\Entity\Pet;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CadastroController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly PetRepository $petRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/cadastro', name: 'cadastro_usuario', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('cadastro/index.html.twig');
    }

    #[Route('/cadastro', name: 'cadastro_usuario_post', methods: ['POST'])]
    public function register(Request $request, Security $security): Response
    {
        $nome = trim($request->request->get('nome', ''));
        $sobrenome = trim($request->request->get('sobrenome', ''));
        $email = trim($request->request->get('email', ''));
        $senha = $request->request->get('senha', '');

        if ($nome === '' || $email === '' || $senha === '') {
            $this->addFlash('error', 'Preencha todos os campos obrigatórios.');
            return $this->redirectToRoute('cadastro_usuario');
        }

        try {
            $user = $this->authService->registerUser(
                $nome . ' ' . $sobrenome,
                $email,
                $senha
            );
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_usuario');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_usuario');
        } catch (\Throwable $e) {
            error_log("CADASTRO ERROR: " . get_class($e) . " - " . $e->getMessage());
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_usuario');
        }

        $this->addFlash('success', 'Conta criada com sucesso!');

        // Salva o ID do usuário na sessão para o próximo request
        $request->getSession()->set('just_registered_user_id', $user->getId());

        return $this->redirectToRoute('cadastro_pet_get');
    }

    #[Route('/cadastro/pet', name: 'cadastro_pet_get', methods: ['GET'])]
    public function pet(Request $request): Response
    {
        $user = $this->getUser();

        // Se não estiver autenticado, tenta buscar pela sessão (auto-login fallback)
        if (!$user instanceof User) {
            $userId = $request->getSession()->get('just_registered_user_id');
            if ($userId) {
                $user = $this->userRepository->find($userId);
                if (!$user instanceof User) {
                    return $this->redirectToRoute('cadastro_usuario');
                }
            } else {
                return $this->redirectToRoute('cadastro_usuario');
            }
        }

        $cliente = $user->getCliente();
        if (!$cliente) {
            $this->addFlash('error', 'Complete seu cadastro antes de adicionar um pet.');
            return $this->redirectToRoute('cadastro_usuario');
        }

        return $this->render('cadastro/pet.html.twig', [
            'pets' => $cliente->getPets(),
        ]);
    }

    #[Route('/cadastro/pet', name: 'cadastro_pet_post', methods: ['POST'])]
    public function registerPet(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $userId = $request->getSession()->get('just_registered_user_id');
            if ($userId) {
                $user = $this->userRepository->find($userId);
                if (!$user instanceof User) {
                    return $this->redirectToRoute('cadastro_usuario');
                }
            } else {
                return $this->redirectToRoute('cadastro_usuario');
            }
        }

        $cliente = $user->getCliente();
        if (!$cliente) {
            $this->addFlash('error', 'Complete seu cadastro antes de adicionar um pet.');
            return $this->redirectToRoute('cadastro_usuario');
        }

        $nome = trim($request->request->get('nome', ''));
        $especie = $request->request->get('especie', 'canino');
        $raca = trim($request->request->get('raca', ''));
        $peso = (float) str_replace(',', '.', $request->request->get('peso', '0'));
        $idade = (int) $request->request->get('idade', '0');

        if ($nome === '') {
            $this->addFlash('error', 'Informe o nome do pet.');
            return $this->redirectToRoute('cadastro_pet_get');
        }

        $tipo = match ($especie) {
            'canino' => PetType::DOG,
            'felino' => PetType::CAT,
            default => throw new \InvalidArgumentException("Espécie inválida: {$especie}"),
        };

        $pet = (new Pet())
            ->setNome($nome)
            ->setCliente($cliente)
            ->setTipo($tipo)
            ->setRaca($raca !== '' ? $raca : null)
            ->setPeso($peso)
            ->setIdadeMeses($idade);

        try {
            $this->petRepository->save($pet, true);
            $this->addFlash('success', 'Pet cadastrado com sucesso!');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erro ao cadastrar pet: ' . $e->getMessage());
        }

        return $this->redirectToRoute('perfil_index');
    }
}
