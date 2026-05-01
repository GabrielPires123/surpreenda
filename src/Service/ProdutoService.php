<?php

namespace App\Service;

use App\Entity\Produto;

/**
 * Serviços de domínio para Produto.
 * Contém regras de negócio que antes estavam na entity.
 */
class ProdutoService
{
    /**
     * Calcula a margem de lucro de um produto.
     */
    public function calcularMargemLucro(Produto $produto): float
    {
        $precoCusto = $produto->getPrecoCusto();
        $precoVenda = $produto->getPrecoVenda();

        if ($precoVenda <= 0) {
            return 0.0;
        }

        return (($precoVenda - $precoCusto) / $precoVenda) * 100;
    }

    /**
     * Verifica se o produto está disponível (tem estoque e está ativo).
     */
    public function isDisponivel(Produto $produto): bool
    {
        return $produto->getEstoque() > 0 && $produto->isAtivo();
    }

    /**
     * Formata o preço de custo para exibição.
     */
    public function getPrecoCustoFormatado(Produto $produto, string $locale = 'pt_BR', string $currency = 'BRL'): string
    {
        return $this->formatarMoeda($produto->getPrecoCusto(), $locale, $currency);
    }

    /**
     * Formata o preço de venda para exibição.
     */
    public function getPrecoVendaFormatado(Produto $produto, string $locale = 'pt_BR', string $currency = 'BRL'): string
    {
        return $this->formatarMoeda($produto->getPrecoVenda(), $locale, $currency);
    }

    private function formatarMoeda(float $valor, string $locale, string $currency): string
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($valor, $currency);
    }
}
