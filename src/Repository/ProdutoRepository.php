<?php

namespace App\Repository;

use App\Entity\Produto;
use App\Repository\Interface\ProdutoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produto>
 */
class ProdutoRepository extends ServiceEntityRepository implements ProdutoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produto::class);
    }

    public function save(Produto $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar produto: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Produto $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover produto: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Produto[] */
    public function findByAtivo(bool $ativo): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.ativo = :ativo')
                ->setParameter('ativo', $ativo)
                ->orderBy('p.nome', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar produtos por status: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Produto[] */
    public function findByCategoria(string $categoriaId): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.categoria = :categoriaId')
                ->setParameter('categoriaId', $categoriaId)
                ->orderBy('p.nome', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar produtos por categoria: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Produto[] */
    public function search(string $query): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.ativo = :ativo')
                ->andWhere('p.nome LIKE :query OR p.descricao LIKE :query')
                ->setParameter('ativo', true)
                ->setParameter('query', '%' . $query . '%')
                ->orderBy('p.nome', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar produtos: ' . $e->getMessage(), 0, $e);
        }
    }
}
