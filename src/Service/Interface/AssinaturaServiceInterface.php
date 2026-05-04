<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Assinatura;
use App\Entity\Cliente;
use App\Entity\Pedido;

interface AssinaturaServiceInterface
{
    public function createAssinatura(Cliente $cliente, mixed $kit, Pedido $pedido): Assinatura;
    public function getProximaCobranca(Assinatura $assinatura): ?\DateTimeImmutable;
    public function isActive(Assinatura $assinatura): bool;
    public function ativar(Assinatura $assinatura): void;
    public function cancelar(Assinatura $assinatura): void;
    public function registrarCobranca(Assinatura $assinatura): void;
    public function isVencida(Assinatura $assinatura): bool;
    public function listAssinaturasByUserId(int $userId): array;
    public function getAssinaturaOrThrow(string $assinaturaId): Assinatura;
}
