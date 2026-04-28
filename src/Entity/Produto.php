<?php

namespace App\Entity;

use App\Repository\ProdutoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProdutoRepository::class)]
#[ORM\Table(name: '`produto`')]
class Produto
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'O nome do produto é obrigatório.')]
    #[Assert\Length(min: 3, max: 100)]
    private string $nome;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(type: 'float')]
    #[Assert\PositiveOrZero(message: 'O preço não pode ser negativo.')]
    private float $precoCusto;

    #[ORM\Column(type: 'float')]
    #[Assert\Positive(message: 'O preço de venda deve ser positivo.')]
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

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = $this->generateUuid();
        $this->createdAt = new \DateTimeImmutable();
        $this->kits = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getMargemLucro(): float
    {
        if ($this->precoCusto <= 0) {
            return 0.0;
        }
        return ($this->precoVenda - $this->precoCusto) / $this->precoCusto * 100;
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

    /**
     * @return Collection<int, Kit>
     */
    public function getKits(): Collection
    {
        return $this->kits;
    }

    public function addKit(Kit $kit): static
    {
        if (!$this->kits->contains($kit)) {
            $this->kits->add($kit);
            $kit->addProduto($this);
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
