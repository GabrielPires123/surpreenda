<?php

namespace App\Repository;

use App\Entity\Produto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produto>
 */
class ProdutoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produto::class);
    }

    public function save(Produto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Produto[]
     */
    public function findAtivos(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.ativo = :ativo')
            ->setParameter('ativo', true)
            ->orderBy('p.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Produto[]
     */
    public function findByCategoriaId(string $categoriaId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.categoria = :categoriaId')
            ->andWhere('p.ativo = :ativo')
            ->setParameter('categoriaId', $categoriaId)
            ->setParameter('ativo', true)
            ->orderBy('p.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
