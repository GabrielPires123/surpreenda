<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\PetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/perfil', name: 'perfil_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class PerfilController extends AbstractController
{
    public function __construct(
        private readonly PetRepository $petRepository,
    ) {
    }
    #[Route('', name: 'index')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('perfil/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/editar', name: 'editar')]
    public function editar(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        return $this->render('perfil/editar.html.twig', [
            'user' => $user,
            'cliente' => $cliente,
        ]);
    }

    #[Route('/pets', name: 'pets')]
    public function meusPets(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        return $this->render('perfil/pets.html.twig', [
            'pets' => $cliente ? $cliente->getPets() : [],
        ]);
    }

    #[Route('/pets/novo', name: 'pet_novo', methods: ['GET'])]
    public function novoPet(): Response
    {
        return $this->render('perfil/pet-novo.html.twig');
    }

    #[Route('/pets/novo', name: 'pet_novo_post', methods: ['POST'])]
    public function salvarNovoPet(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        if (!$cliente) {
            $this->addFlash('error', 'Complete seu cadastro antes de adicionar um pet.');
            return $this->redirectToRoute('perfil_editar');
        }

        $nome = trim($request->request->get('nome', ''));
        $especie = $request->request->get('especie', 'canino');
        $raca = trim($request->request->get('raca', ''));
        $peso = (float) str_replace(',', '.', $request->request->get('peso', '0'));
        $idade = (int) $request->request->get('idade', '0');

        if ($nome === '') {
            $this->addFlash('error', 'Informe o nome do pet.');
            return $this->redirectToRoute('perfil_pet_novo');
        }

        $tipo = match ($especie) {
            'canino' => \App\Enum\PetType::DOG,
            'felino' => \App\Enum\PetType::CAT,
            default => \App\Enum\PetType::DOG,
        };

        $pet = (new \App\Entity\Pet())
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

        return $this->redirectToRoute('perfil_pets');
    }
}
