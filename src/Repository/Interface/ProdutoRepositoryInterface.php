<?php

namespace App\Repository\Interface;

use App\Entity\Produto;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Produto|null find(string $id)
 * @method Produto[] findAll()
 * @method Produto[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Produto|null findOneBy(array $criteria)
 */
interface ProdutoRepositoryInterface extends ObjectRepository
{
    public function save(Produto $entity, bool $flush = false): void;

    public function remove(Produto $entity, bool $flush = false): void;

    /** @return Produto[] */
    public function findByAtivo(bool $ativo): array;

    /** @return Produto[] */
    public function findByCategoria(string $categoriaId): array;

    /** @return Produto[] */
    public function search(string $query): array;
}
