<?php

namespace App\Repository\Interface;

use App\Entity\Cliente;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Cliente|null find(string $id)
 * @method Cliente[] findAll()
 * @method Cliente[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Cliente|null findOneBy(array $criteria)
 */
interface ClienteRepositoryInterface extends ObjectRepository
{
    public function save(Cliente $entity, bool $flush = false): void;

    public function remove(Cliente $entity, bool $flush = false): void;

    public function findOneByCpf(string $cpf): ?Cliente;

    public function findOneByUserId(string $userId): ?Cliente;
}
