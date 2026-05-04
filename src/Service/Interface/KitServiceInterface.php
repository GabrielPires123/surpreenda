<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Kit;

interface KitServiceInterface
{
    public function listKitsDisponiveis(): array;
    public function getKitOrThrow(string $kitId): Kit;
    public function getCustoTotal(Kit $kit): float;
    public function calcularMargemLucro(Kit $kit): float;
    public function isDisponivel(Kit $kit): bool;
    public function getNomesProdutos(Kit $kit): array;
    public function getNomesCategorias(Kit $kit): array;
}
