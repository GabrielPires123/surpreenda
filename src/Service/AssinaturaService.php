<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Assinatura;
use App\Entity\Cliente;
use App\Entity\Kit;
use App\Entity\Pedido;
use App\Entity\Pet;
use App\Enum\SubscriptionStatus;
use App\Repository\Interface\AssinaturaRepositoryInterface;
use App\Repository\Interface\ClienteRepositoryInterface;
use App\Service\Interface\AssinaturaServiceInterface;

class AssinaturaService implements AssinaturaServiceInterface
{
    public function __construct(
        private AssinaturaRepositoryInterface $assinaturaRepository,
        private ClienteRepositoryInterface    $clienteRepository,
    ) {
    }

    public function createAssinatura(Cliente $cliente, Kit $kit, Pedido $pedido): Assinatura
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

    public function getProximaCobranca(Assinatura $assinatura): ?\DateTimeImmutable
    {
        if ($assinatura->getStatus() !== SubscriptionStatus::ACTIVE) {
            return null;
        }

        $baseDate = $assinatura->getDataUltimaCobranca() ?? $assinatura->getDataInicio();
        return $baseDate->modify('+' . $assinatura->getIntervaloDias() . ' days');
    }

    public function isActive(Assinatura $assinatura): bool
    {
        return $assinatura->getStatus() === SubscriptionStatus::ACTIVE;
    }

    public function ativar(Assinatura $assinatura): void
    {
        if ($assinatura->getStatus() === SubscriptionStatus::EXPIRED) {
            throw new \DomainException('Não é possível ativar uma assinatura expirada.');
        }

        $assinatura->setStatus(SubscriptionStatus::ACTIVE);
    }

    public function cancelar(Assinatura $assinatura): void
    {
        if ($assinatura->getStatus() === SubscriptionStatus::CANCELLED) {
            throw new \DomainException('A assinatura já está cancelada.');
        }

        $assinatura->setStatus(SubscriptionStatus::CANCELLED);
        $assinatura->setDataFim(new \DateTimeImmutable());
    }

    public function registrarCobranca(Assinatura $assinatura): void
    {
        $assinatura->setDataUltimaCobranca(new \DateTimeImmutable());
    }

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

    public function simulateSubscription(Pet $pet, Kit $kit): array
    {
        $precoMensal = $kit->getPreco();
        $precoAnual = $precoMensal * 12;
        $descontoAnual = $precoAnual * 0.15;
        $precoAnualComDesconto = $precoAnual - $descontoAnual;

        return [
            'pet' => $pet->getNome(),
            'kit' => $kit->getNome(),
            'mensal' => $precoMensal,
            'anual' => $precoAnualComDesconto,
            'economia_anual' => $descontoAnual,
            'proxima_cobranca' => $this->calcularProximaCobranca(30),
        ];
    }

    public function createSubscription(int $userId, Pet $pet, Kit $kit): Assinatura
    {
        $cliente = $this->getClienteOrThrow($userId);

        $pedido = (new Pedido())
            ->setCliente($cliente)
            ->setKit($kit)
            ->setPet($pet)
            ->setValorTotal($kit->getPreco())
            ->setStatus(\App\Enum\OrderStatus::PENDING)
            ->setDataPedido(new \DateTimeImmutable())
            ->initUuid();

        return $this->createAssinatura($cliente, $kit, $pedido);
    }

    public function getSubscriptionOrThrow(string $assinaturaId): Assinatura
    {
        return $this->getAssinaturaOrThrow($assinaturaId);
    }

    public function getUserSubscriptions(int $userId): array
    {
        return $this->listAssinaturasByUserId($userId);
    }

    public function cancelSubscription(Assinatura $assinatura): void
    {
        $this->cancelar($assinatura);
    }

    public function pauseSubscription(Assinatura $assinatura): void
    {
        if ($assinatura->getStatus() !== SubscriptionStatus::ACTIVE) {
            throw new \DomainException('Somente assinaturas ativas podem ser pausadas.');
        }

        $assinatura->setStatus(SubscriptionStatus::PAUSED);
    }

    public function listAssinaturasByUserId(int $userId): array
    {
        $cliente = $this->getClienteOrThrow($userId);

        return $this->assinaturaRepository->findByClienteId($cliente->getId());
    }

    public function getAssinaturaOrThrow(string $assinaturaId): Assinatura
    {
        $assinatura = $this->assinaturaRepository->find($assinaturaId);
        if ($assinatura === null) {
            throw new \InvalidArgumentException('Assinatura não encontrada.');
        }

        return $assinatura;
    }

    private function getClienteOrThrow(int $userId): Cliente
    {
        $cliente = $this->clienteRepository->findOneByUserId($userId);
        if ($cliente === null) {
            throw new \InvalidArgumentException('Cliente não encontrado.');
        }

        return $cliente;
    }

    private function calcularProximaCobranca(int $intervaloDias): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->modify('+' . $intervaloDias . ' days');
    }
}
