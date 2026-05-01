<?php

namespace App\Repository\Interface;

use App\Entity\Endereco;
use Doctrine\Persistence\ObjectRepository;

/**
 * @method Endereco|null find(string $id)
 * @method Endereco[] findAll()
 * @method Endereco[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 * @method Endereco|null findOneBy(array $criteria)
 */
interface EnderecoRepositoryInterface extends ObjectRepository
{
    public function save(Endereco $entity, bool $flush = false): void;

    public function remove(Endereco $entity, bool $flush = false): void;

    /** @return Endereco[] */
    public function findByClienteId(string $clienteId): array;
}
