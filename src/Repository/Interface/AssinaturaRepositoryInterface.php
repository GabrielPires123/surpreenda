<?php

namespace App\Repository\Interface;

use App\Entity\Assinatura;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Assinatura|null find(string $id)
 * @method Assinatura[] findAll()
 * @method Assinatura[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Assinatura|null findOneBy(array $criteria)
 */
interface AssinaturaRepositoryInterface extends ObjectRepository
{
    public function save(Assinatura $entity, bool $flush = false): void;

    public function remove(Assinatura $entity, bool $flush = false): void;

    /** @return Assinatura[] */
    public function findByClienteId(string $clienteId): array;
}
