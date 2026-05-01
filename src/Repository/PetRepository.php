<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Enum\PetType;
use App\Repository\Interface\PetRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pet>
 */
class PetRepository extends ServiceEntityRepository implements PetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pet::class);
    }

    public function save(Pet $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar pet: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(Pet $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover pet: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Pet[] */
    public function findByTipo(PetType $tipo): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.tipo = :tipo')
                ->setParameter('tipo', $tipo->value)
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar pets por tipo: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return Pet[] */
    public function findByClienteId(string $clienteId): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.cliente = :clienteId')
                ->setParameter('clienteId', $clienteId)
                ->orderBy('p.nome', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar pets do cliente: ' . $e->getMessage(), 0, $e);
        }
    }
}
