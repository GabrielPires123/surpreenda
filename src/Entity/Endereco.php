<?php

namespace App\Entity;

use App\Repository\EnderecoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnderecoRepository::class)]
#[ORM\Table(name: '`endereco`')]
class Endereco
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank(message: 'O CEP é obrigatório.')]
    #[Assert\Regex(pattern: '/^\d{5}-?\d{3}$/', message: 'CEP inválido.')]
    private string $cep;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'O logradouro é obrigatório.')]
    private string $logradouro;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'O número é obrigatório.')]
    private string $numero;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $complemento = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'O bairro é obrigatório.')]
    private string $bairro;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'A cidade é obrigatória.')]
    private string $cidade;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank(message: 'O estado é obrigatório.')]
    #[Assert\Length(min: 2, max: 2)]
    private string $estado;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'enderecos')]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column]
    private bool $isPrincipal = false;

    public function __construct()
    {
        $this->id = $this->generateUuid();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCep(): string
    {
        return $this->cep;
    }

    public function setCep(string $cep): static
    {
        $this->cep = str_replace('-', '', $cep);
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
        $this->estado = strtoupper($estado);
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
        return sprintf(
            '%s, %s%s - %s, %s - %s',
            $this->logradouro,
            $this->numero,
            $this->complemento ? ' - ' . $this->complemento : '',
            $this->bairro,
            $this->cidade,
            $this->estado
        );
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
