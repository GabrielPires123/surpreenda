<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Enum\SubscriptionStatus;
use App\Repository\AssinaturaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssinaturaRepository::class)]
#[ORM\Table(name: '`assinatura`')]
class Assinatura
{
    use UuidTrait;

    #[ORM\Column(length: 50)]
    private string $plano;

    #[ORM\Column(type: 'float')]
    private float $valor;

    #[ORM\Column(enumType: SubscriptionStatus::class)]
    private SubscriptionStatus $status;

    #[ORM\Column]
    private int $intervaloDias;

    #[ORM\OneToOne(inversedBy: 'assinatura', targetEntity: Cliente::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dataInicio;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataFim = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataUltimaCobranca = null;

    public function __construct()
    {
        $this->initUuid();
        $this->status = SubscriptionStatus::PENDING;
        $this->dataInicio = new \DateTimeImmutable();
    }

    public function getPlano(): string
    {
        return $this->plano;
    }

    public function setPlano(string $plano): static
    {
        $this->plano = $plano;
        return $this;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function setValor(float $valor): static
    {
        $this->valor = $valor;
        return $this;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getIntervaloDias(): int
    {
        return $this->intervaloDias;
    }

    public function setIntervaloDias(int $intervaloDias): static
    {
        $this->intervaloDias = $intervaloDias;
        return $this;
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

    public function getDataInicio(): \DateTimeImmutable
    {
        return $this->dataInicio;
    }

    public function setDataInicio(\DateTimeImmutable $dataInicio): static
    {
        $this->dataInicio = $dataInicio;
        return $this;
    }

    public function getDataFim(): ?\DateTimeImmutable
    {
        return $this->dataFim;
    }

    public function setDataFim(?\DateTimeImmutable $dataFim): static
    {
        $this->dataFim = $dataFim;
        return $this;
    }

    public function getDataUltimaCobranca(): ?\DateTimeImmutable
    {
        return $this->dataUltimaCobranca;
    }

    public function setDataUltimaCobranca(?\DateTimeImmutable $dataUltimaCobranca): static
    {
        $this->dataUltimaCobranca = $dataUltimaCobranca;
        return $this;
    }

    public function getProximaCobranca(): ?\DateTimeImmutable
    {
        if ($this->dataUltimaCobranca === null) {
            return $this->dataInicio->modify('+' . $this->intervaloDias . ' days');
        }
        return $this->dataUltimaCobranca->modify('+' . $this->intervaloDias . ' days');
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            SubscriptionStatus::PENDING => 'Pendente',
            SubscriptionStatus::ACTIVE => 'Ativa',
            SubscriptionStatus::CANCELLED => 'Cancelada',
            SubscriptionStatus::EXPIRED => 'Expirada',
        };
    }
}
