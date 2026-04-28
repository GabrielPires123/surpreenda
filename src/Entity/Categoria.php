<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
#[ORM\Table(name: '`categoria`')]
class Categoria
{
    use UuidTrait;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'O nome da categoria é obrigatório.')]
    #[Assert\Length(min: 3, max: 50)]
    private string $nome;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $iconeUrl = null;

    #[ORM\Column]
    private int $ordem = 0;

    /** @var Collection<int, Produto> */
    #[ORM\OneToMany(targetEntity: Produto::class, mappedBy: 'categoria')]
    private Collection $produtos;

    /** @var Collection<int, Kit> */
    #[ORM\ManyToMany(targetEntity: Kit::class, mappedBy: 'categorias')]
    private Collection $kits;

    public function __construct()
    {
        $this->initUuid();
        $this->produtos = new ArrayCollection();
        $this->kits = new ArrayCollection();
    }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): static { $this->nome = $nome; return $this; }

    public function getDescricao(): ?string { return $this->descricao; }
    public function setDescricao(?string $descricao): static { $this->descricao = $descricao; return $this; }

    public function getIconeUrl(): ?string { return $this->iconeUrl; }
    public function setIconeUrl(?string $iconeUrl): static { $this->iconeUrl = $iconeUrl; return $this; }

    public function getOrdem(): int { return $this->ordem; }
    public function setOrdem(int $ordem): static { $this->ordem = $ordem; return $this; }

    /** @return Collection<int, Produto> */
    public function getProdutos(): Collection { return $this->produtos; }

    public function addProduto(Produto $produto): static
    {
        if (!$this->produtos->contains($produto)) {
            $this->produtos->add($produto);
            $produto->setCategoria($this);
        }
        return $this;
    }

    public function removeProduto(Produto $produto): static
    {
        $this->produtos->removeElement($produto);
        return $this;
    }

    /** @return Collection<int, Kit> */
    public function getKits(): Collection { return $this->kits; }

    public function addKit(Kit $kit): static
    {
        if (!$this->kits->contains($kit)) {
            $this->kits->add($kit);
            $kit->addCategoria($this);
        }
        return $this;
    }

    public function removeKit(Kit $kit): static
    {
        $this->kits->removeElement($kit);
        return $this;
    }
}
