<?php

namespace App\Entity;

use App\Repository\TelefoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TelefoneRepository::class)]
#[ORM\Table(name: '`telefone`')]
class Telefone
{
    use UuidTrait;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{2}$/', message: 'DDD deve ter 2 dígitos.')]
    private string $ddd;

    #[ORM\Column(length: 9)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{8,9}$/', message: 'Número de telefone inválido.')]
    private string $numero;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'telefones')]
    #[ORM\JoinColumn(nullable: false)]
    private Cliente $cliente;

    #[ORM\Column(length: 20)]
    private string $tipo = 'mobile';

    public function __construct()
    {
        $this->initUuid();
    }

    public function getDdd(): string { return $this->ddd; }
    public function setDdd(string $ddd): static { $this->ddd = preg_replace('/\D/', '', $ddd); return $this; }

    public function getNumero(): string { return $this->numero; }
    public function setNumero(string $numero): static { $this->numero = preg_replace('/\D/', '', $numero); return $this; }

    public function getFullNumber(): string { return '(' . $this->ddd . ') ' . $this->numero; }

    public function getCliente(): Cliente { return $this->cliente; }
    public function setCliente(Cliente $cliente): static { $this->cliente = $cliente; return $this; }

    public function getTipo(): string { return $this->tipo; }
    public function setTipo(string $tipo): static { $this->tipo = $tipo; return $this; }
}
