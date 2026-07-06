<?php

namespace App\Controller\Front;

use App\Repository\KitRepository;
use App\Repository\ProdutoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CarrinhoController extends AbstractController
{
    private const SESSION_CART = 'cart';
    private const SESSION_COUNT = 'cart_count';

    public function __construct(
        private readonly KitRepository $kitRepository,
        private readonly ProdutoRepository $produtoRepository,
    ) {
    }

    #[Route('/carrinho', name: 'carrinho_index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $cartItems = $this->getCartItems($session);
        $kitsData = [];

        foreach ($cartItems as $id => $item) {
            $entity = $this->findEntity($id, $item['type'] ?? 'kit');
            if ($entity) {
                $kitsData[$id] = [
                    'entity' => $entity,
                    'type' => $item['type'] ?? 'kit',
                    'quantidade' => $item['qty'] ?? 1,
                ];
            }
        }

        return $this->render('carrinho/index.html.twig', [
            'kitsData' => $kitsData,
            'total' => $this->calcularTotal($kitsData),
        ]);
    }

    // --- JSON API endpoints ---

    #[Route('/api/cart/sync', name: 'api_cart_sync', methods: ['POST'])]
    public function sync(Request $request, SessionInterface $session): JsonResponse
    {
        $clientCart = $request->toArray()['items'] ?? [];

        if (!is_array($clientCart)) {
            return new JsonResponse(['count' => $this->getCartCount($session)]);
        }

        // Merge client items into session
        foreach ($clientCart as $item) {
            if (isset($item['id']) && isset($item['qty'])) {
                $type = $item['type'] ?? 'kit';
                $id = $item['id'];
                $qty = (int) $item['qty'];

                if ($qty <= 0) {
                    continue;
                }

                $this->addToSession($session, $id, $type, $qty);
            }
        }

        return new JsonResponse(['count' => $this->getCartCount($session)]);
    }

    #[Route('/api/cart/count', name: 'api_cart_count', methods: ['GET'])]
    public function count(SessionInterface $session): JsonResponse
    {
        return new JsonResponse(['count' => $this->getCartCount($session)]);
    }

    #[Route('/api/cart/add', name: 'api_cart_add', methods: ['POST'])]
    public function addJson(Request $request, SessionInterface $session): JsonResponse
    {
        $data = $request->toArray();
        $id = $data['id'] ?? null;
        $type = $data['type'] ?? 'kit';
        $qty = (int) ($data['qty'] ?? 1);

        if (!$id) {
            return new JsonResponse(['error' => 'Missing id'], 400);
        }

        // Validate entity exists
        $entity = $this->findEntity($id, $type);
        if (!$entity) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }

        $isActive = $type === 'kit'
            ? ($entity->isAtivo() ?? true)
            : ($entity->isAtivo() ?? true);

        if (!$isActive) {
            return new JsonResponse(['error' => 'Entity unavailable'], 400);
        }

        $this->addToSession($session, $id, $type, $qty);
        $count = $this->getCartCount($session);

        return new JsonResponse(['count' => $count]);
    }

    #[Route('/api/cart/remove/{id}', name: 'api_cart_remove', methods: ['POST'])]
    public function removeJson(string $id, SessionInterface $session): JsonResponse
    {
        $cart = $session->get(self::SESSION_CART, []);
        unset($cart[$id]);
        $session->set(self::SESSION_CART, $cart);

        return new JsonResponse(['count' => $this->getCartCount($session)]);
    }

    #[Route('/api/cart/update/{id}', name: 'api_cart_update', methods: ['POST'])]
    public function updateJson(string $id, Request $request, SessionInterface $session): JsonResponse
    {
        $data = $request->toArray();
        $qty = (int) ($data['qty'] ?? 1);

        $cart = $session->get(self::SESSION_CART, []);

        if ($qty <= 0) {
            unset($cart[$id]);
        } elseif (isset($cart[$id])) {
            $cart[$id]['qty'] = $qty;
        }

        $session->set(self::SESSION_CART, $cart);

        return new JsonResponse(['count' => $this->getCartCount($session)]);
    }

    #[Route('/api/cart/clear', name: 'api_cart_clear', methods: ['POST'])]
    public function clearJson(SessionInterface $session): JsonResponse
    {
        $session->remove(self::SESSION_CART);
        return new JsonResponse(['count' => 0]);
    }

    // --- Legacy full-page redirects (for show pages that use <a> links) ---

    #[Route('/carrinho/adicionar/{kitId}', name: 'carrinho_adicionar', methods: ['GET'])]
    public function adicionar(string $kitId, SessionInterface $session, Request $request): Response
    {
        $kit = $this->kitRepository->find($kitId);
        if (!$kit || !$kit->isAtivo()) {
            $this->addFlash('error', 'Kit não encontrado ou indisponível.');
            return $this->redirectToRoute('kit_index');
        }

        $this->addToSession($session, $kitId, 'kit', 1);
        $this->addFlash('success', 'Kit adicionado ao carrinho!');

        return $this->redirectToRoute('carrinho_index');
    }

    #[Route('/carrinho/remover/{kitId}', name: 'carrinho_remover', methods: ['GET'])]
    public function remover(string $kitId, SessionInterface $session): Response
    {
        $cart = $session->get(self::SESSION_CART, []);
        unset($cart[$kitId]);
        $session->set(self::SESSION_CART, $cart);
        $this->addFlash('success', 'Item removido do carrinho.');

        return $this->redirectToRoute('carrinho_index');
    }

    #[Route('/carrinho/atualizar/{kitId}', name: 'carrinho_atualizar', methods: ['POST'])]
    public function atualizar(string $kitId, Request $request, SessionInterface $session): Response
    {
        $qty = (int) $request->request->get('quantidade', 1);
        $cart = $session->get(self::SESSION_CART, []);

        if ($qty <= 0) {
            unset($cart[$kitId]);
        } elseif (isset($cart[$kitId])) {
            $cart[$kitId]['qty'] = $qty;
        }

        $session->set(self::SESSION_CART, $cart);
        $this->addFlash('success', 'Carrinho atualizado.');

        return $this->redirectToRoute('carrinho_index');
    }

    #[Route('/carrinho/limpar', name: 'carrinho_limpar', methods: ['POST'])]
    public function limpar(SessionInterface $session): Response
    {
        $session->remove(self::SESSION_CART);
        $this->addFlash('success', 'Carrinho limpo.');

        return $this->redirectToRoute('carrinho_index');
    }

    // --- Private helpers ---

    private function findEntity(string $id, string $type): ?object
    {
        return match ($type) {
            'produto' => $this->produtoRepository->find($id),
            default => $this->kitRepository->find($id),
        };
    }

    private function addToSession(SessionInterface $session, string $id, string $type, int $qty): void
    {
        $cart = $session->get(self::SESSION_CART, []);

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = ['id' => $id, 'type' => $type, 'qty' => $qty];
        }

        $session->set(self::SESSION_CART, $cart);
    }

    private function getCartItems(SessionInterface $session): array
    {
        return $session->get(self::SESSION_CART, []);
    }

    private function getCartCount(SessionInterface $session): int
    {
        $cart = $this->getCartItems($session);
        $total = 0;

        foreach ($cart as $item) {
            if (is_array($item) && isset($item['qty'])) {
                $total += (int) $item['qty'];
            } elseif (is_numeric($item)) {
                $total += (int) $item;
            }
        }

        return $total;
    }

    private function calcularTotal(array $kitsData): float
    {
        $total = 0;

        foreach ($kitsData as $data) {
            $entity = $data['entity'];
            $qty = $data['quantidade'];

            if (method_exists($entity, 'getPreco')) {
                $total += $entity->getPreco() * $qty;
            }
        }

        return $total;
    }
}
