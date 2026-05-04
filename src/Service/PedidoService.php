<?php

namespace App\Service;

use App\Entity\Assinatura;
use App\Entity\Cliente;
use App\Entity\Kit;
use App\Entity\Pedido;
use App\Entity\Pet;
use App\Enum\OrderStatus;
use App\Enum\SubscriptionStatus;
use App\Repository\Interface\AssinaturaRepositoryInterface;
use App\Repository\Interface\ClienteRepositoryInterface;
use App\Repository\Interface\PedidoRepositoryInterface;
use App\Service\Interface\PedidoServiceInterface;
use App\Validator\EntityValidator;

class PedidoService implements PedidoServiceInterface
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly ClienteRepositoryInterface $clienteRepository,
        private readonly AssinaturaRepositoryInterface $assinaturaRepository,
        private readonly EntityValidator $entityValidator,
    ) {
    }

    /**
     * Domain: Create a new Pedido from kit+pet
     */
    public function createPedido(Cliente $cliente, Kit $kit, Pet $pet): Pedido
    {
        $pedido = (new Pedido())
            ->setCliente($cliente)
            ->setKit($kit)
            ->setPet($pet)
            ->setValorTotal($kit->getPreco())
            ->setStatus(OrderStatus::PENDING)
            ->setDataPedido(new \DateTimeImmutable())
            ->initUuid();

        $this->entityValidator->validatePedido($pedido);

        return $pedido;
    }

    /**
     * Domain: Checkout — create pedido + assinatura in one transaction
     */
    public function checkout(int $userId, Kit $kit, Pet $pet): array
    {
        $cliente = $this->getClienteOrThrow($userId);

        // Validate pet belongs to cliente
        if ($pet->getCliente()->getId() !== $cliente->getId()) {
            throw new \DomainException('O pet selecionado não pertence ao seu cliente.');
        }

        $pedido = $this->createPedido($cliente, $kit, $pet);

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

        $this->pedidoRepository->save($pedido, true);
        $this->assinaturaRepository->save($assinatura, true);

        return [
            'pedido' => $pedido,
            'assinatura' => $assinatura,
        ];
    }

    /**
     * Repository: List all pedidos for a user
     */
    public function listPedidosByUserId(int $userId): array
    {
        $cliente = $this->getClienteOrThrow($userId);

        return $this->pedidoRepository->findBy(['cliente' => $cliente], ['dataPedido' => 'DESC']);
    }

    /**
     * Repository: Find a single pedido by ID
     */
    public function getPedidoOrThrow(string $pedidoId): Pedido
    {
        $pedido = $this->pedidoRepository->find($pedidoId);
        if ($pedido === null) {
            throw new \InvalidArgumentException('Pedido não encontrado.');
        }

        return $pedido;
    }

    /**
     * Domain: Cancel a pedido
     */
    public function cancelarPedido(string $pedidoId): Pedido
    {
        $pedido = $this->getPedidoOrThrow($pedidoId);

        if ($pedido->getStatus() === OrderStatus::DELIVERED || $pedido->getStatus() === OrderStatus::CANCELLED) {
            throw new \DomainException('Pedido não pode ser cancelado neste status.');
        }

        $pedido->setStatus(OrderStatus::CANCELLED);
        $this->pedidoRepository->save($pedido, true);

        return $pedido;
    }

    /**
     * Domain: Track a pedido
     */
    public function trackPedido(string $pedidoId): Pedido
    {
        return $this->getPedidoOrThrow($pedidoId);
    }

    private function getClienteOrThrow(int $userId): Cliente
    {
        $cliente = $this->clienteRepository->findOneByUserId($userId);
        if ($cliente === null) {
            throw new \InvalidArgumentException('Cliente não encontrado.');
        }

        return $cliente;
    }
}
