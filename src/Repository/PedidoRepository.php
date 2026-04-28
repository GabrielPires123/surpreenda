<?php

namespace App\Repository;

use App\Entity\Pedido;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pedido>
 */
class PedidoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedido::class);
    }

    public function save(Pedido $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pedido $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Pedido[]
     */
    public function findByClienteId(string $clienteId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cliente = :clienteId')
            ->setParameter('clienteId', $clienteId)
            ->orderBy('p.dataPedido', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Pedido[]
     */
    public function findByStatus(OrderStatus $status): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status->value)
            ->orderBy('p.dataPedido', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(OrderStatus $status): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Pedido[]
     */
    public function findPendentes(): array
    {
        return $this->findByStatus(OrderStatus::PENDING);
    }
}
