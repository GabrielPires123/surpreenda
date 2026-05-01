<?php

namespace App\Repository;

use App\Entity\Pedido;
use App\Repository\Interface\PedidoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pedido>
 */
class PedidoRepository extends ServiceEntityRepository implements PedidoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedido::class);
    }

    public function save(Pedido $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar pedido: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Pedido $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover pedido: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Pedido[] */
    public function findByClienteId(string $clienteId): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.cliente = :clienteId')
                ->setParameter('clienteId', $clienteId)
                ->orderBy('p.dataPedido', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar pedidos do cliente: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Pedido[] */
    public function findByStatus(string $status): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.status = :status')
                ->setParameter('status', $status)
                ->orderBy('p.dataPedido', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar pedidos por status: ' . $e->getMessage(), 0, $e);
        }
    }
}
