<?php

namespace App\Repository;

use App\Entity\Endereco;
use App\Repository\Interface\EnderecoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Endereco>
 */
class EnderecoRepository extends ServiceEntityRepository implements EnderecoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Endereco::class);
    }

    public function save(Endereco $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar endereço: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Endereco $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover endereço: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Endereco[] */
    public function findByClienteId(string $clienteId): array
    {
        try {
            return $this->createQueryBuilder('e')
                ->andWhere('e.cliente = :clienteId')
                ->setParameter('clienteId', $clienteId)
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar endereços do cliente: ' . $e->getMessage(), 0, $e);
        }
    }
}
