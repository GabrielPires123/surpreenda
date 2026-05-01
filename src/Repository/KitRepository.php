<?php

namespace App\Repository;

use App\Entity\Kit;
use App\Repository\Interface\KitRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Kit>
 */
class KitRepository extends ServiceEntityRepository implements KitRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kit::class);
    }

    public function save(Kit $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar kit: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Kit $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover kit: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Kit[] */
    public function findDisponiveis(): array
    {
        try {
            return $this->createQueryBuilder('k')
                ->andWhere('k.ativo = :ativo')
                ->setParameter('ativo', true)
                ->orderBy('k.nome', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar kits disponíveis: ' . $e->getMessage(), 0, $e);
        }
    }
}
