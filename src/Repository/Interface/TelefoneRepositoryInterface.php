<?php

namespace App\Repository\Interface;

use App\Entity\Telefone;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Telefone|null find(string $id)
 * @method Telefone[] findAll()
 * @method Telefone[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Telefone|null findOneBy(array $criteria)
 */
interface TelefoneRepositoryInterface extends ObjectRepository
{
    public function save(Telefone $entity, bool $flush = false): void;

    public function remove(Telefone $entity, bool $flush = false): void;

    /** @return Telefone[] */
    public function findByClienteId(string $clienteId): array;
}
