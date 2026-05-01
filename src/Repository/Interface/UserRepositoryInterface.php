<?php

namespace App\Repository\Interface;

use App\Entity\User;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method User|null find(string $id)
 * @method User[] findAll()
 * @method User[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method User|null findOneBy(array $criteria)
 */
interface UserRepositoryInterface extends ObjectRepository
{
    public function save(User $entity, bool $flush = false): void;

    public function remove(User $entity, bool $flush = false): void;

    public function findOneByEmail(string $email): ?User;
}
