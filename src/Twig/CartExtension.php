<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\CarrinhoService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class CartExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly CarrinhoService $carrinhoService,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'cartCount' => $this->carrinhoService->getCartCount(),
        ];
    }
}
