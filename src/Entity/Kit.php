<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\KitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KitRepository::class)]
#[ORM\Table(name: '`kit`')]
class Kit
{
    use UuidTrait;

    #[ORM\Column(length: 100)]
    private string $nome;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(type: 'float')]
    private float $preco;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagemUrl = null;

    #[ORM\Column]
    private int $quantidadeItens;

    /** @var Collection<int, Produto> */
    #[ORM\ManyToMany(targetEntity: Produto::class, inversedBy: 'kits')]
    #[ORM\JoinTable(name: 'kit_produto')]
    private Collection $produtos;

    /** @var Collection<int, Categoria> */
    #[ORM\ManyToMany(targetEntity: Categoria::class, inversedBy: 'kits')]
    #[ORM\JoinTable(name: 'kit_categoria')]
    private Collection $categorias;

    #[ORM\Column]
    private bool $ativo = true;

    public function __construct()
    {
        $this->initUuid();
        $this->produtos = new ArrayCollection();
        $this->categorias = new ArrayCollection();
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

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function setPreco(float $preco): static
    {
        $this->preco = $preco;
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

    public function getQuantidadeItens(): int
    {
        return $this->quantidadeItens;
    }

    public function setQuantidadeItens(int $quantidadeItens): static
    {
        $this->quantidadeItens = $quantidadeItens;
        return $this;
    }

    /** @return Collection<int, Produto> */
    public function getProdutos(): Collection
    {
        return $this->produtos;
    }

    public function addProduto(Produto $produto): static
    {
        if (!$this->produtos->contains($produto)) {
            $this->produtos->add($produto);
        }
        return $this;
    }

    public function removeProduto(Produto $produto): static
    {
        $this->produtos->removeElement($produto);
        return $this;
    }

    /** @return Collection<int, Categoria> */
    public function getCategorias(): Collection
    {
        return $this->categorias;
    }

    public function addCategoria(Categoria $categoria): static
    {
        if (!$this->categorias->contains($categoria)) {
            $this->categorias->add($categoria);
        }
        return $this;
    }

    public function removeCategoria(Categoria $categoria): static
    {
        $this->categorias->removeElement($categoria);
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
