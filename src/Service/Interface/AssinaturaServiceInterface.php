<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Assinatura;
use App\Entity\Cliente;
use App\Entity\Kit;
use App\Entity\Pedido;
use App\Entity\Pet;

interface AssinaturaServiceInterface
{
    public function createAssinatura(Cliente $cliente, Kit $kit, Pedido $pedido): Assinatura;
    public function getProximaCobranca(Assinatura $assinatura): ?\DateTimeImmutable;
    public function isActive(Assinatura $assinatura): bool;
    public function ativar(Assinatura $assinatura): void;
    public function cancelar(Assinatura $assinatura): void;
    public function registrarCobranca(Assinatura $assinatura): void;
    public function isVencida(Assinatura $assinatura): bool;
    public function simulateSubscription(Pet $pet, Kit $kit): array;
    public function createSubscription(int $userId, Pet $pet, Kit $kit): Assinatura;
    public function getSubscriptionOrThrow(string $assinaturaId): Assinatura;
    public function getUserSubscriptions(int $userId): array;
    public function cancelSubscription(Assinatura $assinatura): void;
    public function pauseSubscription(Assinatura $assinatura): void;
    public function listAssinaturasByUserId(int $userId): array;
    public function getAssinaturaOrThrow(string $assinaturaId): Assinatura;
}
