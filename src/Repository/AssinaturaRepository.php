<?php

namespace App\Repository;

use App\Entity\Assinatura;
use App\Repository\Interface\AssinaturaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assinatura>
 */
class AssinaturaRepository extends ServiceEntityRepository implements AssinaturaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assinatura::class);
    }

    public function save(Assinatura $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar assinatura: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Assinatura $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover assinatura: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Assinatura[] */
    public function findByClienteId(string $clienteId): array
    {
        try {
            return $this->createQueryBuilder('a')
                ->andWhere('a.cliente = :clienteId')
                ->setParameter('clienteId', $clienteId)
                ->orderBy('a.dataInicio', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar assinaturas do cliente: ' . $e->getMessage(), 0, $e);
        }
    }
}
