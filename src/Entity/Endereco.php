<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\EnderecoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnderecoRepository::class)]
#[ORM\Table(name: '`endereco`')]
class Endereco
{
    use UuidTrait;

    #[ORM\Column(length: 8)]
    private string $cep;

    #[ORM\Column(length: 100)]
    private string $logradouro;

    #[ORM\Column(length: 10)]
    private string $numero;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $complemento = null;

    #[ORM\Column(length: 50)]
    private string $bairro;

    #[ORM\Column(length: 50)]
    private string $cidade;

    #[ORM\Column(length: 2)]
    private string $estado;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'enderecos')]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column]
    private bool $isPrincipal = false;

    public function __construct()
    {
        $this->initUuid();
    }

    public function getCep(): string
    {
        return $this->cep;
    }

    public function setCep(string $cep): static
    {
        $this->cep = $cep;
        return $this;
    }

    public function getLogradouro(): string
    {
        return $this->logradouro;
    }

    public function setLogradouro(string $logradouro): static
    {
        $this->logradouro = $logradouro;
        return $this;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getComplemento(): ?string
    {
        return $this->complemento;
    }

    public function setComplemento(?string $complemento): static
    {
        $this->complemento = $complemento;
        return $this;
    }

    public function getBairro(): string
    {
        return $this->bairro;
    }

    public function setBairro(string $bairro): static
    {
        $this->bairro = $bairro;
        return $this;
    }

    public function getCidade(): string
    {
        return $this->cidade;
    }

    public function setCidade(string $cidade): static
    {
        $this->cidade = $cidade;
        return $this;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;
        return $this;
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): static
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function isPrincipal(): bool
    {
        return $this->isPrincipal;
    }

    public function setIsPrincipal(bool $isPrincipal): static
    {
        $this->isPrincipal = $isPrincipal;
        return $this;
    }

    public function getFullAddress(): string
    {
        $address = $this->logradouro . ', ' . $this->numero;
        if ($this->complemento) {
            $address .= ' - ' . $this->complemento;
        }
        $address .= ', ' . $this->bairro;
        $address .= ', ' . $this->cidade . ' - ' . $this->estado;
        $address .= ', CEP: ' . $this->cep;
        return $address;
    }

    public function __toString(): string
    {
        return $this->getFullAddress();
    }
}
