<?php

namespace App\Entity;

use App\Enum\SubscriptionStatus;
use App\Repository\AssinaturaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssinaturaRepository::class)]
#[ORM\Table(name: '`assinatura`')]
class Assinatura
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\OneToOne(inversedBy: 'assinatura', targetEntity: Cliente::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column(enumType: SubscriptionStatus::class)]
    private SubscriptionStatus $status;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'O plano é obrigatório.')]
    private string $plano;

    #[ORM\Column(type: 'float')]
    #[Assert\Positive(message: 'O valor deve ser positivo.')]
    private float $valor;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive(message: 'O intervalo deve ser positivo.')]
    private int $intervaloDias;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dataInicio;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataFim = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataProximoEnvio = null;

    #[ORM\Column]
    private int $renovacoes = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = $this->generateUuid();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = SubscriptionStatus::ACTIVE;
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

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionStatus $status): static
    {
        $this->status = $status;
        return $this;
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

    public function getIntervaloDias(): int
    {
        return $this->intervaloDias;
    }

    public function setIntervaloDias(int $intervaloDias): static
    {
        $this->intervaloDias = $intervaloDias;
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

    public function getDataProximoEnvio(): ?\DateTimeImmutable
    {
        return $this->dataProximoEnvio;
    }

    public function setDataProximoEnvio(?\DateTimeImmutable $dataProximoEnvio): static
    {
        $this->dataProximoEnvio = $dataProximoEnvio;
        return $this;
    }

    public function getRenovacoes(): int
    {
        return $this->renovacoes;
    }

    public function incrementarRenovacao(): static
    {
        $this->renovacoes++;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isAtiva(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
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
