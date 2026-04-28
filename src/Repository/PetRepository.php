<?php

namespace App\Repository;

use App\Entity\Pet;
use App\Enum\PetType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pet>
 */
class PetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pet::class);
    }

    public function save(Pet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return Pet[] */
    public function findByTipo(PetType $tipo): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.tipo = :tipo')
            ->setParameter('tipo', $tipo->value)
            ->getQuery()
            ->getResult();
    }

    /** @return Pet[] */
    public function findByClienteId(string $clienteId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cliente = :clienteId')
            ->setParameter('clienteId', $clienteId)
            ->orderBy('p.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
