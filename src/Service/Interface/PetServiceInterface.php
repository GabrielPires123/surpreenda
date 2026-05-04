<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Pet;
use App\Enum\PetType;

interface PetServiceInterface
{
    public function getPetsByUserId(int $userId): array;
    public function getPetById(string $petId): ?Pet;
    public function createPet(int $userId, string $nome, PetType $tipo, ?string $raca, ?float $peso, \DateTimeImmutable $dataNascimento): Pet;
    public function updatePet(string $petId, array $data): Pet;
    public function deletePet(string $petId): void;
}
