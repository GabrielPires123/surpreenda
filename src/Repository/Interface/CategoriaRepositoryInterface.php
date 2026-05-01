<?php

namespace App\Repository\Interface;

use App\Entity\Categoria;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Categoria|null find(string $id)
 * @method Categoria[] findAll()
 * @method Categoria[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Categoria|null findOneBy(array $criteria)
 */
interface CategoriaRepositoryInterface extends ObjectRepository
{
    public function save(Categoria $entity, bool $flush = false): void;

    public function remove(Categoria $entity, bool $flush = false): void;

    public function findByNome(string $nome): ?Categoria;

    /** @return Categoria[] */
    public function findAllAtivas(): array;
}
