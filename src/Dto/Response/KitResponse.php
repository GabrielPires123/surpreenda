<?php

namespace App\Dto\Response;

use App\Entity\Kit;

class KitResponse
{
    public string $id;
    public string $nome;
    public ?string $descricao = null;
    public float $preco;
    public ?string $imagemUrl = null;
    public int $quantidadeItens;
    public bool $ativo;
    /** @var string[] */
    public array $produtos = [];
    /** @var string[] */
    public array $categorias = [];

    public function __construct(
        string $id,
        string $nome,
        ?string $descricao,
        float $preco,
        ?string $imagemUrl,
        int $quantidadeItens,
        bool $ativo,
        array $produtos,
        array $categorias
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->preco = $preco;
        $this->imagemUrl = $imagemUrl;
        $this->quantidadeItens = $quantidadeItens;
        $this->ativo = $ativo;
        $this->produtos = $produtos;
        $this->categorias = $categorias;
    }

    public static function fromEntity(Kit $entity): self
    {
        $produtos = [];
        foreach ($entity->getProdutos() as $produto) {
            $produtos[] = $produto->getNome();
        }

        $categorias = [];
        foreach ($entity->getCategorias() as $categoria) {
            $categorias[] = $categoria->getNome();
        }

        return new self(
            $entity->getId(),
            $entity->getNome(),
            $entity->getDescricao(),
            $entity->getPreco(),
            $entity->getImagemUrl(),
            $entity->getQuantidadeItens(),
            $entity->isAtivo(),
            $produtos,
            $categorias
        );
    }
}
