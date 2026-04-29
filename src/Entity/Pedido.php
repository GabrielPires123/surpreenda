<?php

namespace App\Entity;

use App\Entity\Trait\AnonymizableTrait;
use App\Entity\Trait\UuidTrait;
use App\Enum\OrderStatus;
use App\Repository\PedidoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
#[ORM\Table(name: '`pedido`')]
class Pedido
{
    use UuidTrait;
    use AnonymizableTrait;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'pedidos')]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\ManyToOne(targetEntity: Kit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Kit $kit;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status;

    #[ORM\Column(type: 'float')]
    private float $valorTotal;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $valorDesconto = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $codigoRastreio = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dataPedido;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataEnvio = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataEntrega = null;

    public function __construct()
    {
        $this->initUuid();
        $this->status = OrderStatus::PENDING;
        $this->dataPedido = new \DateTimeImmutable();
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function setCliente(Cliente $cliente): static
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getKit(): Kit
    {
        return $this->kit;
    }

    public function setKit(Kit $kit): static
    {
        $this->kit = $kit;
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getValorTotal(): float
    {
        return $this->valorTotal;
    }

    public function setValorTotal(float $valorTotal): static
    {
        $this->valorTotal = $valorTotal;
        return $this;
    }

    public function getValorDesconto(): ?float
    {
        return $this->valorDesconto;
    }

    public function setValorDesconto(?float $valorDesconto): static
    {
        $this->valorDesconto = $valorDesconto;
        return $this;
    }

    public function getObservacoes(): ?string
    {
        return $this->observacoes;
    }

    public function setObservacoes(?string $observacoes): static
    {
        $this->observacoes = $observacoes;
        return $this;
    }

    public function getCodigoRastreio(): ?string
    {
        return $this->codigoRastreio;
    }

    public function setCodigoRastreio(?string $codigoRastreio): static
    {
        $this->codigoRastreio = $codigoRastreio;
        return $this;
    }

    public function getDataPedido(): \DateTimeImmutable
    {
        return $this->dataPedido;
    }

    public function setDataPedido(\DateTimeImmutable $dataPedido): static
    {
        $this->dataPedido = $dataPedido;
        return $this;
    }

    public function getDataEnvio(): ?\DateTimeImmutable
    {
        return $this->dataEnvio;
    }

    public function setDataEnvio(?\DateTimeImmutable $dataEnvio): static
    {
        $this->dataEnvio = $dataEnvio;
        return $this;
    }

    public function getDataEntrega(): ?\DateTimeImmutable
    {
        return $this->dataEntrega;
    }

    public function setDataEntrega(?\DateTimeImmutable $dataEntrega): static
    {
        $this->dataEntrega = $dataEntrega;
        return $this;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            OrderStatus::PENDING => 'Pendente',
            OrderStatus::CONFIRMED => 'Confirmado',
            OrderStatus::SHIPPED => 'Enviado',
            OrderStatus::DELIVERED => 'Entregue',
            OrderStatus::CANCELLED => 'Cancelado',
        };
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    public function isDelivered(): bool
    {
        return $this->status === OrderStatus::DELIVERED;
    }
}
