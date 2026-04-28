<?php

namespace App\Entity;

use App\Enum\PetType;
use App\Repository\PetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PetRepository::class)]
#[ORM\Table(name: '`pet`')]
class Pet
{
    use UuidTrait;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'O nome do pet é obrigatório.')]
    #[Assert\Length(min: 2, max: 50)]
    private string $nome;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'pets')]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column(enumType: PetType::class)]
    #[Assert\NotNull(message: 'O tipo do pet é obrigatório.')]
    private PetType $tipo;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $raca = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'O peso deve ser um valor positivo.')]
    private float $peso;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'A idade deve ser zero ou positiva.')]
    private int $idadeMeses;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fotoUrl = null;

    public function __construct()
    {
        $this->initUuid();
    }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): static { $this->nome = $nome; return $this; }

    public function getCliente(): Cliente { return $this->cliente; }
    public function setCliente(Cliente $cliente): static { $this->cliente = $cliente; return $this; }

    public function getTipo(): PetType { return $this->tipo; }
    public function setTipo(PetType $tipo): static { $this->tipo = $tipo; return $this; }

    public function getRaca(): ?string { return $this->raca; }
    public function setRaca(?string $raca): static { $this->raca = $raca; return $this; }

    public function getPeso(): float { return $this->peso; }
    public function setPeso(float $peso): static { $this->peso = $peso; return $this; }

    public function getIdadeMeses(): int { return $this->idadeMeses; }
    public function setIdadeMeses(int $idadeMeses): static { $this->idadeMeses = $idadeMeses; return $this; }

    public function getFotoUrl(): ?string { return $this->fotoUrl; }
    public function setFotoUrl(?string $fotoUrl): static { $this->fotoUrl = $fotoUrl; return $this; }

    public function isDog(): bool { return $this->tipo === PetType::DOG; }
    public function isCat(): bool { return $this->tipo === PetType::CAT; }
}
