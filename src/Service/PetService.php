<?php

namespace App\Service;

use App\Enum\PetType;
use App\Entity\Pet;
use App\Entity\Cliente;
use App\Repository\Interface\ClienteRepositoryInterface;
use App\Repository\Interface\PetRepositoryInterface;
use App\Validator\EntityValidator;

class PetService
{
    public function __construct(
        private readonly PetRepositoryInterface $petRepository,
        private readonly ClienteRepositoryInterface $clienteRepository,
        private readonly EntityValidator $entityValidator,
    ) {
    }

    public function getPetsByUserId(int $userId): array
    {
        $cliente = $this->getClienteByUserId($userId);

        return $this->petRepository->findByClienteId($cliente->getId());
    }

    public function getPetById(string $petId): ?Pet
    {
        return $this->petRepository->find($petId);
    }

    public function createPet(int $userId, string $nome, PetType $tipo, ?string $raca, ?float $peso, \DateTimeImmutable $dataNascimento): Pet
    {
        $cliente = $this->getClienteByUserId($userId);

        $pet = (new Pet())
            ->setNome($nome)
            ->setTipo($tipo)
            ->setRaca($raca)
            ->setPeso($peso)
            ->setDataNascimento($dataNascimento)
            ->setCliente($cliente)
            ->initUuid();

        $this->entityValidator->validatePet($pet);

        $this->petRepository->save($pet, true);

        return $pet;
    }

    public function updatePet(string $petId, array $data): Pet
    {
        $pet = $this->getPetOrThrow($petId);

        if (isset($data['nome'])) {
            $pet->setNome($data['nome']);
        }
        if (isset($data['tipo'])) {
            $pet->setTipo($data['tipo']);
        }
        if (isset($data['raca'])) {
            $pet->setRaca($data['raca']);
        }
        if (isset($data['peso'])) {
            $pet->setPeso((float) $data['peso']);
        }
        if (isset($data['dataNascimento'])) {
            $pet->setDataNascimento(new \DateTimeImmutable($data['dataNascimento']));
        }

        $this->entityValidator->validatePet($pet);

        $this->petRepository->save($pet, true);

        return $pet;
    }

    public function deletePet(string $petId): void
    {
        $pet = $this->getPetOrThrow($petId);
        $this->petRepository->remove($pet, true);
    }

    private function getClienteByUserId(int $userId): Cliente
    {
        $cliente = $this->clienteRepository->findOneByUserId($userId);
        if ($cliente === null) {
            throw new \InvalidArgumentException('Cliente não encontrado para este usuário.');
        }

        return $cliente;
    }

    private function getPetOrThrow(string $petId): Pet
    {
        $pet = $this->petRepository->find($petId);
        if ($pet === null) {
            throw new \InvalidArgumentException('Pet não encontrado.');
        }

        return $pet;
    }
}
