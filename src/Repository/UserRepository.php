<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Interface\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao salvar usuário: ' . $e->getMessage(), 0, $e);
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($entity);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new \RuntimeException('Erro ao remover usuário: ' . $e->getMessage(), 0, $e);
        }
    }

    public function findOneByEmail(string $email): ?User
    {
        try {
            return $this->createQueryBuilder('u')
                ->andWhere('u.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \RuntimeException('Erro ao buscar usuário por email: ' . $e->getMessage(), 0, $e);
        }
    }
}
