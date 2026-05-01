<?php

namespace App\Repository\Interface;

use App\Entity\Pedido;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Pedido|null find(string $id)
 * @method Pedido[] findAll()
 * @method Pedido[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Pedido|null findOneBy(array $criteria)
 */
interface PedidoRepositoryInterface extends ObjectRepository
{
    public function save(Pedido $entity, bool $flush = false): void;

    public function remove(Pedido $entity, bool $flush = false): void;

    /** @return Pedido[] */
    public function findByClienteId(string $clienteId): array;

    /** @return Pedido[] */
    public function findByStatus(string $status): array;
}
