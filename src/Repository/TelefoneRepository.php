<?php

namespace App\Repository;

use App\Entity\Telefone;
use App\Repository\Interface\TelefoneRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Telefone>
 */
class TelefoneRepository extends ServiceEntityRepository implements TelefoneRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Telefone::class);
    }

    public function save(Telefone $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar telefone: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Telefone $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover telefone: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Telefone[] */
    public function findByClienteId(string $clienteId): array
    {
        try {
            return $this->createQueryBuilder('t')
                ->andWhere('t.cliente = :clienteId')
                ->setParameter('clienteId', $clienteId)
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar telefones do cliente: ' . $e->getMessage(), 0, $e);
        }
    }
}
