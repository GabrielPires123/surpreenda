<?php

namespace App\Repository;

use App\Entity\Categoria;
use App\Repository\Interface\CategoriaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categoria>
 */
class CategoriaRepository extends ServiceEntityRepository implements CategoriaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoria::class);
    }

    public function save(Categoria $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar categoria: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Categoria $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover categoria: ' . $e->getMessage(), 0, $e);
        }
    }

    public function findByNome(string $nome): ?Categoria
    {
        try {
            return $this->createQueryBuilder('c')
                ->andWhere('c.nome = :nome')
                ->setParameter('nome', $nome)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar categoria por nome: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Categoria[] */
    public function findAllAtivas(): array
    {
        try {
            return $this->createQueryBuilder('c')
                ->orderBy('c.ordem', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar categorias ativas: ' . $e->getMessage(), 0, $e);
        }
    }
}
