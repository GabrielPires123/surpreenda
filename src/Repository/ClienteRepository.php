<?php

namespace App\Repository;

use App\Entity\Cliente;
use App\Repository\Interface\ClienteRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
class ClienteRepository extends ServiceEntityRepository implements ClienteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    public function save(Cliente $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar cliente: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Cliente $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover cliente: ' . $e->getMessage(), 0, $e);
        }
    }

    public function findOneByCpf(string $cpf): ?Cliente
    {
        try {
            return $this->createQueryBuilder('c')
                ->andWhere('c.cpf = :cpf')
                ->setParameter('cpf', $cpf)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar cliente por CPF: ' . $e->getMessage(), 0, $e);
        }
    }

    public function findOneByUserId(string $userId): ?Cliente
    {
        try {
            return $this->createQueryBuilder('c')
                ->join('c.user', 'u')
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar cliente por usuário: ' . $e->getMessage(), 0, $e);
        }
    }
}
