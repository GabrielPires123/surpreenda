<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\Interface\PedidoRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/meus-pedidos', name: 'pedido_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class PedidoController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PedidoRepositoryInterface $pedidoRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        if (!$cliente) {
            $this->addFlash('info', 'Complete seu cadastro para ver seus pedidos.');
            return $this->redirectToRoute('cadastro_pet_get');
        }

        $pedidos = $pedidoRepository->findByClienteId($cliente->getId());

        // Ordenar por data mais recente primeiro
        usort($pedidos, fn($a, $b) => $b->getDataCriacao() <=> $a->getDataCriacao());

        return $this->render('pedido/index.html.twig', [
            'pedidos' => $pedidos,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(string $id, PedidoRepositoryInterface $pedidoRepository): Response
    {
        $pedido = $pedidoRepository->find($id);

        if (!$pedido) {
            throw $this->createNotFoundException('Pedido não encontrado.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Garantir que o pedido pertence ao usuário
        if ($pedido->getCliente()->getId() !== $user->getCliente()->getId()) {
            throw $this->createAccessDeniedException('Você não tem permissão para ver este pedido.');
        }

        return $this->render('pedido/show.html.twig', [
            'pedido' => $pedido,
        ]);
    }
}
