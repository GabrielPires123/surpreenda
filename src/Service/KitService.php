<?php

namespace App\Service;

use App\Entity\Kit;
use App\Entity\Produto;
use App\Repository\Interface\KitRepositoryInterface;
use App\Service\Interface\KitServiceInterface;

class KitService implements KitServiceInterface
{
    public function __construct(
        private readonly KitRepositoryInterface $kitRepository,
    ) {
    }

    /**
     * Repository: List all available kits
     */
    public function listKitsDisponiveis(): array
    {
        return $this->kitRepository->findDisponiveis();
    }

    /**
     * Repository: Find a single kit by ID
     */
    public function getKitOrThrow(string $kitId): Kit
    {
        $kit = $this->kitRepository->find($kitId);
        if ($kit === null) {
            throw new \InvalidArgumentException('Kit não encontrado.');
        }

        return $kit;
    }

    /**
     * Domain: Calculate cost of products in kit
     */
    public function getCustoTotal(Kit $kit): float
    {
        $custoTotal = 0.0;
        foreach ($kit->getProdutos() as $produto) {
            $custoTotal += $produto->getPrecoCusto();
        }
        return $custoTotal;
    }

    /**
     * Domain: Calculate profit margin
     */
    public function calcularMargemLucro(Kit $kit): float
    {
        $custoTotal = $this->getCustoTotal($kit);
        $precoKit = $kit->getPreco();

        if ($precoKit <= 0) {
            return 0.0;
        }

        return (($precoKit - $custoTotal) / $precoKit) * 100;
    }

    /**
     * Domain: Check if kit is available
     */
    public function isDisponivel(Kit $kit): bool
    {
        if (!$kit->isAtivo()) {
            return false;
        }

        foreach ($kit->getProdutos() as $produto) {
            if ($produto->getEstoque() <= 0 || !$produto->isAtivo()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Domain: Get product names from kit
     */
    public function getNomesProdutos(Kit $kit): array
    {
        return $kit->getProdutos()
            ->map(fn(Produto $p) => $p->getNome())
            ->toArray();
    }

    /**
     * Domain: Get category names from kit
     */
    public function getNomesCategorias(Kit $kit): array
    {
        return $kit->getCategorias()
            ->map(fn($c) => $c->getNome())
            ->toArray();
    }
}
