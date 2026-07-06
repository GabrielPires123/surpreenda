<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session-backed cart service.
 * Source of truth for cart state. localStorage is used as a local cache only.
 */
class CarrinhoService
{
    private const SESSION_CART = 'cart';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getCartCount(): int
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_CART, []);

        if (!is_array($cart)) {
            return 0;
        }

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

    public function getCartItems(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::SESSION_CART, []);
    }

    public function addItem(string $id, string $type = 'kit', int $qty = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_CART, []);

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = ['id' => $id, 'type' => $type, 'qty' => $qty];
        }

        $session->set(self::SESSION_CART, $cart);
    }

    public function removeItem(string $id): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_CART, []);
        unset($cart[$id]);
        $session->set(self::SESSION_CART, $cart);
    }

    public function updateQuantity(string $id, int $qty): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_CART, []);

        if ($qty <= 0) {
            unset($cart[$id]);
        } elseif (isset($cart[$id])) {
            $cart[$id]['qty'] = $qty;
        }

        $session->set(self::SESSION_CART, $cart);
    }

    public function clear(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove(self::SESSION_CART);
    }
}
