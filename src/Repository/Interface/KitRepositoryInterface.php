<?php

namespace App\Repository\Interface;

use App\Entity\Kit;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Kit|null find(string $id)
 * @method Kit[] findAll()
 * @method Kit[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Kit|null findOneBy(array $criteria)
 */
interface KitRepositoryInterface extends ObjectRepository
{
    public function save(Kit $entity, bool $flush = false): void;

    public function remove(Kit $entity, bool $flush = false): void;

    /** @return Kit[] */
    public function findDisponiveis(): array;
}
