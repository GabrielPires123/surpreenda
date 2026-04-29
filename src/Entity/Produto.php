<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\ProdutoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProdutoRepository::class)]
#[ORM\Table(name: '`produto`')]
class Produto
{
    use UuidTrait;

    #[ORM\Column(length: 100)]
    private string $nome;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(type: 'float')]
    private float $precoCusto;

    #[ORM\Column(type: 'float')]
    private float $precoVenda;

    #[ORM\Column]
    private int $estoque = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagemUrl = null;

    #[ORM\ManyToOne(targetEntity: Categoria::class, inversedBy: 'produtos')]
    #[ORM\JoinColumn(nullable: false)]
    private Categoria $categoria;

    /** @var Collection<int, Kit> */
    #[ORM\ManyToMany(targetEntity: Kit::class, mappedBy: 'produtos')]
    private Collection $kits;

    #[ORM\Column]
    private bool $ativo = true;

    public function __construct()
    {
        $this->initUuid();
        $this->kits = new ArrayCollection();
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;
        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): static
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function getPrecoCusto(): float
    {
        return $this->precoCusto;
    }

    public function setPrecoCusto(float $precoCusto): static
    {
        $this->precoCusto = $precoCusto;
        return $this;
    }

    public function getPrecoVenda(): float
    {
        return $this->precoVenda;
    }

    public function setPrecoVenda(float $precoVenda): static
    {
        $this->precoVenda = $precoVenda;
        return $this;
    }

    public function getEstoque(): int
    {
        return $this->estoque;
    }

    public function setEstoque(int $estoque): static
    {
        $this->estoque = $estoque;
        return $this;
    }

    public function getImagemUrl(): ?string
    {
        return $this->imagemUrl;
    }

    public function setImagemUrl(?string $imagemUrl): static
    {
        $this->imagemUrl = $imagemUrl;
        return $this;
    }

    public function getCategoria(): Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(Categoria $categoria): static
    {
        $this->categoria = $categoria;
        return $this;
    }

    /** @return Collection<int, Kit> */
    public function getKits(): Collection
    {
        return $this->kits;
    }

    public function addKit(Kit $kit): static
    {
        if (!$this->kits->contains($kit)) {
            $this->kits->add($kit);
        }
        return $this;
    }

    public function removeKit(Kit $kit): static
    {
        $this->kits->removeElement($kit);
        return $this;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): static
    {
        $this->ativo = $ativo;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nome;
    }
}
