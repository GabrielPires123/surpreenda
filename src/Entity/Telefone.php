<?php

namespace App\Entity;

use App\Repository\TelefoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TelefoneRepository::class)]
#[ORM\Table(name: '`telefone`')]
class Telefone
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

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
        $this->id = $this->generateUuid();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDdd(): string
    {
        return $this->ddd;
    }

    public function setDdd(string $ddd): static
    {
        $this->ddd = preg_replace('/\D/', '', $ddd);
        return $this;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = preg_replace('/\D/', '', $numero);
        return $this;
    }

    public function getFullNumber(): string
    {
        return '(' . $this->ddd . ') ' . $this->numero;
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

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
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
