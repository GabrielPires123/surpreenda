<?php

namespace App\Service;

use App\Entity\Assinatura;
use App\Entity\Cliente;
use App\Entity\Pedido;
use App\Enum\SubscriptionStatus;
use App\Repository\Interface\AssinaturaRepositoryInterface;
use App\Repository\Interface\ClienteRepositoryInterface;

class AssinaturaService
{
    public function __construct(
        private readonly AssinaturaRepositoryInterface $assinaturaRepository,
        private readonly ClienteRepositoryInterface $clienteRepository,
    ) {
    }

    /**
     * Domain: Create a new Assinatura from kit+pedido
     */
    public function createAssinatura(Cliente $cliente, mixed $kit, Pedido $pedido): Assinatura
    {
        $assinatura = (new Assinatura())
            ->setPlano($kit->getNome())
            ->setValor($kit->getPreco())
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setIntervaloDias(30)
            ->setDataInicio(new \DateTimeImmutable())
            ->setDataUltimaCobranca(new \DateTimeImmutable())
            ->setCliente($cliente)
            ->setPedido($pedido)
            ->initUuid();

        return $assinatura;
    }

    /**
     * Domain: Calculate next billing date
     */
    public function getProximaCobranca(Assinatura $assinatura): ?\DateTimeImmutable
    {
        if ($assinatura->getStatus() !== SubscriptionStatus::ACTIVE) {
            return null;
        }

        $baseDate = $assinatura->getDataUltimaCobranca() ?? $assinatura->getDataInicio();
        return $baseDate->modify('+' . $assinatura->getIntervaloDias() . ' days');
    }

    /**
     * Domain: Check if subscription is active
     */
    public function isActive(Assinatura $assinatura): bool
    {
        return $assinatura->getStatus() === SubscriptionStatus::ACTIVE;
    }

    /**
     * Domain: Activate subscription
     */
    public function ativar(Assinatura $assinatura): void
    {
        if ($assinatura->getStatus() === SubscriptionStatus::EXPIRED) {
            throw new \DomainException('Não é possível ativar uma assinatura expirada.');
        }

        $assinatura->setStatus(SubscriptionStatus::ACTIVE);
    }

    /**
     * Domain: Cancel subscription
     */
    public function cancelar(Assinatura $assinatura): void
    {
        if ($assinatura->getStatus() === SubscriptionStatus::CANCELLED) {
            throw new \DomainException('A assinatura já está cancelada.');
        }

        $assinatura->setStatus(SubscriptionStatus::CANCELLED);
        $assinatura->setDataFim(new \DateTimeImmutable());
    }

    /**
     * Domain: Register a billing event
     */
    public function registrarCobranca(Assinatura $assinatura): void
    {
        $assinatura->setDataUltimaCobranca(new \DateTimeImmutable());
    }

    /**
     * Domain: Check if subscription is expired
     */
    public function isVencida(Assinatura $assinatura): bool
    {
        if ($assinatura->getDataFim() !== null) {
            return true;
        }

        $proximaCobranca = $this->getProximaCobranca($assinatura);
        if ($proximaCobranca === null) {
            return false;
        }

        return $proximaCobranca < new \DateTimeImmutable();
    }

    /**
     * Repository: List all subscriptions for a user
     */
    public function listAssinaturasByUserId(int $userId): array
    {
        $cliente = $this->getClienteOrThrow($userId);

        return $this->assinaturaRepository->findByClienteId($cliente->getId());
    }

    /**
     * Repository: Find a single subscription by ID
     */
    public function getAssinaturaOrThrow(string $assinaturaId): Assinatura
    {
        $assinatura = $this->assinaturaRepository->find($assinaturaId);
        if ($assinatura === null) {
            throw new \InvalidArgumentException('Assinatura não encontrada.');
        }

        return $assinatura;
    }

    private function getClienteOrThrow(int $userId): mixed
    {
        $cliente = $this->clienteRepository->findOneByUserId($userId);
        if ($cliente === null) {
            throw new \InvalidArgumentException('Cliente não encontrado.');
        }

        return $cliente;
    }
}
