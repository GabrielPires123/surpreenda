<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
#[ORM\Table(name: '`cliente`')]
class Cliente
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\Column(length: 11, unique: true, nullable: true)]
    #[Assert\Regex(pattern: '/^\d{11}$/', message: 'CPF inválido.')]
    private ?string $cpf = null;

    #[ORM\OneToOne(inversedBy: 'cliente', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /** @var Collection<int, Endereco> */
    #[ORM\OneToMany(targetEntity: Endereco::class, mappedBy: 'cliente', orphanRemoval: true, cascade: ['persist'])]
    private Collection $enderecos;

    /** @var Collection<int, Telefone> */
    #[ORM\OneToMany(targetEntity: Telefone::class, mappedBy: 'cliente', orphanRemoval: true, cascade: ['persist'])]
    private Collection $telefones;

    /** @var Collection<int, Pet> */
    #[ORM\OneToMany(targetEntity: Pet::class, mappedBy: 'cliente', orphanRemoval: true, cascade: ['persist'])]
    private Collection $pets;

    /** @var Collection<int, Pedido> */
    #[ORM\OneToMany(targetEntity: Pedido::class, mappedBy: 'cliente')]
    private Collection $pedidos;

    #[ORM\OneToOne(targetEntity: Assinatura::class, mappedBy: 'cliente', cascade: ['persist', 'remove'])]
    private ?Assinatura $assinatura = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $anonymizedAt = null;

    public function __construct()
    {
        $this->id = $this->generateUuid();
        $this->createdAt = new \DateTimeImmutable();
        $this->enderecos = new ArrayCollection();
        $this->telefones = new ArrayCollection();
        $this->pets = new ArrayCollection();
        $this->pedidos = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(?string $cpf): static
    {
        $this->cpf = preg_replace('/\D/', '', $cpf);
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Endereco>
     */
    public function getEnderecos(): Collection
    {
        return $this->enderecos;
    }

    public function getEnderecoPrincipal(): ?Endereco
    {
        foreach ($this->enderecos as $endereco) {
            if ($endereco->isPrincipal()) {
                return $endereco;
            }
        }
        return $this->enderecos->first() ?: null;
    }

    public function addEndereco(Endereco $endereco): static
    {
        if (!$this->enderecos->contains($endereco)) {
            $this->enderecos->add($endereco);
            $endereco->setCliente($this);
        }
        return $this;
    }

    public function removeEndereco(Endereco $endereco): static
    {
        if ($this->enderecos->removeElement($endereco)) {
            if ($endereco->getCliente() === $this) {
                $endereco->setCliente(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Telefone>
     */
    public function getTelefones(): Collection
    {
        return $this->telefones;
    }

    public function addTelefone(Telefone $telefone): static
    {
        if (!$this->telefones->contains($telefone)) {
            $this->telefones->add($telefone);
            $telefone->setCliente($this);
        }
        return $this;
    }

    public function removeTelefone(Telefone $telefone): static
    {
        $this->telefones->removeElement($telefone);
        return $this;
    }

    /**
     * @return Collection<int, Pet>
     */
    public function getPets(): Collection
    {
        return $this->pets;
    }

    public function addPet(Pet $pet): static
    {
        if (!$this->pets->contains($pet)) {
            $this->pets->add($pet);
            $pet->setCliente($this);
        }
        return $this;
    }

    public function removePet(Pet $pet): static
    {
        $this->pets->removeElement($pet);
        return $this;
    }

    /**
     * @return Collection<int, Pedido>
     */
    public function getPedidos(): Collection
    {
        return $this->pedidos;
    }

    public function addPedido(Pedido $pedido): static
    {
        if (!$this->pedidos->contains($pedido)) {
            $this->pedidos->add($pedido);
            $pedido->setCliente($this);
        }
        return $this;
    }

    public function getAssinatura(): ?Assinatura
    {
        return $this->assinatura;
    }

    public function setAssinatura(?Assinatura $assinatura): static
    {
        $this->assinatura = $assinatura;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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
