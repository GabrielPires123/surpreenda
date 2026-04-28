<?php

namespace App\Repository;

use App\Entity\Assinatura;
use App\Enum\SubscriptionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assinatura>
 */
class AssinaturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assinatura::class);
    }

    public function save(Assinatura $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Assinatura $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Assinatura[]
     */
    public function findByStatus(SubscriptionStatus $status): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', $status->value)
            ->getQuery()
            ->getResult();
    }

    public function findAtivas(): array
    {
        return $this->findByStatus(SubscriptionStatus::ACTIVE);
    }

    /**
     * Find assinaturas ativas cujo próximo envio está pendente.
     *
     * @return Assinatura[]
     */
    public function findPendentesEnvio(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.dataProximoEnvio <= :now')
            ->setParameter('status', SubscriptionStatus::ACTIVE->value)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.dataProximoEnvio', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
