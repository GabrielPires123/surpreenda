<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Pedido;
use App\Repository\KitRepository;
use App\Repository\PetRepository;
use App\Repository\ProdutoRepository;
use App\Service\CarrinhoService;
use App\Service\PedidoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout', name: 'checkout_')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly PedidoService $pedidoService,
        private readonly CarrinhoService $carrinhoService,
        private readonly KitRepository $kitRepository,
        private readonly ProdutoRepository $produtoRepository,
        private readonly PetRepository $petRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $cartItems = $this->carrinhoService->getCartItems();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Seu carrinho está vazio.');
            return $this->redirectToRoute('carrinho_index');
        }

        $kits = [];
        $products = [];
        $total = 0;

        foreach ($cartItems as $id => $item) {
            $type = $item['type'] ?? 'kit';
            $qty = $item['qty'] ?? 1;
            $entity = $this->findEntity($id, $type);

            if (!$entity) {
                continue;
            }

            if ($type === 'produto') {
                $products[] = [
                    'id' => $id,
                    'entity' => $entity,
                    'qty' => $qty,
                ];
            } else {
                $kits[] = [
                    'id' => $id,
                    'entity' => $entity,
                    'qty' => $qty,
                ];
            }

            if (method_exists($entity, 'getPreco')) {
                $total += $entity->getPreco() * $qty;
            }
        }

        if (empty($kits) && empty($products)) {
            $this->addFlash('warning', 'Nenhum item válido no carrinho.');
            return $this->redirectToRoute('carrinho_index');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        $pets = $cliente?->getPets()?->toArray() ?? [];

        if (empty($pets)) {
            $this->addFlash('warning', 'Você precisa cadastrar um pet antes de finalizar a assinatura.');
            return $this->redirectToRoute('cadastro_pet_get');
        }

        return $this->render('checkout/index.html.twig', [
            'kits' => $kits,
            'products' => $products,
            'pets' => $pets,
            'total' => $total,
        ]);
    }

    #[Route('/confirmar', name: 'confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        $kitData = $request->request->all('kits');
        $firstKit = $kitData[0] ?? null;

        if (!$firstKit || !isset($firstKit['kit_id']) || !isset($firstKit['pet_id'])) {
            $this->addFlash('error', 'Dados incompletos para finalizar o pedido.');
            return $this->redirectToRoute('checkout_index');
        }

        $kitId = $firstKit['kit_id'];
        $petId = $firstKit['pet_id'];

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $kit = $this->kitRepository->find($kitId);
        $pet = $this->petRepository->find($petId);

        if (!$kit || !$pet) {
            $this->addFlash('error', 'Kit ou pet não encontrado.');
            return $this->redirectToRoute('checkout_index');
        }

        try {
            $pedido = $this->pedidoService->checkout(
                $user->getId(),
                $kit,
                $pet
            );

            // Clear cart after successful checkout
            $this->carrinhoService->clear();

            $this->addFlash('success', sprintf(
                'Pedido #%s criado com sucesso! Assinatura ativa.',
                $pedido->getId()
            ));

            return $this->redirectToRoute('checkout_success', ['id' => $pedido->getId()]);
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('checkout_index');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cadastro_pet_get');
        }
    }

    #[Route('/sucesso/{id}', name: 'success', methods: ['GET'])]
    public function success(string $id): Response
    {
        try {
            $pedido = $this->pedidoService->getPedidoOrThrow($id);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', 'Pedido não encontrado.');
            return $this->redirectToRoute('home_index');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cliente = $user->getCliente();

        // Security: only the owner can view
        if ($pedido->getCliente()->getId() !== $cliente->getId()) {
            throw $this->createAccessDeniedException('Você não tem permissão para ver este pedido.');
        }

        return $this->render('checkout/success.html.twig', [
            'pedido' => $pedido,
            'assinatura' => $pedido->getAssinatura(),
        ]);
    }

    private function findEntity(string $id, string $type): ?object
    {
        return match ($type) {
            'produto' => $this->produtoRepository->find($id),
            default => $this->kitRepository->find($id),
        };
    }
}
