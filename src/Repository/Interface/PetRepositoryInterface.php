<?php

namespace App\Repository\Interface;

use App\Entity\Pet;
use App\Enum\PetType;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Pet|null find(string $id)
 * @method Pet[] findAll()
 * @method Pet[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Pet|null findOneBy(array $criteria)
 */
interface PetRepositoryInterface extends ObjectRepository
{
    public function save(Pet $entity, bool $flush = false): void;

    public function remove(Pet $entity, bool $flush = false): void;

    /** @return Pet[] */
    public function findByTipo(PetType $tipo): array;

    /** @return Pet[] */
    public function findByClienteId(string $clienteId): array;
}
