<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Produto;

interface ProdutoServiceInterface
{
    public function calcularMargemLucro(Produto $produto): float;
    public function isDisponivel(Produto $produto): bool;
    public function getPrecoCustoFormatado(Produto $produto, string $locale = 'pt_BR', string $currency = 'BRL'): string;
    public function getPrecoVendaFormatado(Produto $produto, string $locale = 'pt_BR', string $currency = 'BRL'): string;
}
