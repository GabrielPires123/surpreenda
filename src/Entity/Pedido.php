<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\PedidoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
#[ORM\Table(name: '`pedido`')]
class Pedido
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'pedidos')]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\ManyToOne(targetEntity: Kit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Kit $kit;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status;

    #[ORM\Column(type: 'float')]
    #[Assert\Positive(message: 'O valor total deve ser positivo.')]
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

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $anonymizedAt = null;

    public function __construct()
    {
        $this->id = $this->generateUuid();
        $this->status = OrderStatus::PENDING;
        $this->dataPedido = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
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
        $this->valorTotal = $kit->getPreco();
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

    public function getValorFinal(): float
    {
        if ($this->valorDesconto === null) {
            return $this->valorTotal;
        }
        return max(0, $this->valorTotal - $this->valorDesconto);
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

    public function setDataEnvio(?\DateTimeImmutable $dataEnvio): static
    {
        $this->dataEnvio = $dataEnvio;
        return $this;
    }

    public function getDataEnvio(): ?\DateTimeImmutable
    {
        return $this->dataEnvio;
    }

    public function setDataEntrega(?\DateTimeImmutable $dataEntrega): static
    {
        $this->dataEntrega = $dataEntrega;
        return $this;
    }

    public function getDataEntrega(): ?\DateTimeImmutable
    {
        return $this->dataEntrega;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAnonymizedAt(): ?\DateTimeImmutable
    {
        return $this->anonymizedAt;
    }

    public function setAnonymizedAt(?\DateTimeImmutable $anonymizedAt): static
    {
        $this->anonymizedAt = $anonymizedAt;
        return $this;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
