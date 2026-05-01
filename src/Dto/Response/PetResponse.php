<?php

namespace App\Dto\Response;

use App\Entity\Pet;

class PetResponse
{
    public string $id;
    public string $nome;
    public string $tipo;
    public ?string $raca = null;
    public ?float $peso = null;
    public string $dataNascimento;
    public ?string $fotoUrl = null;

    public static function fromEntity(Pet $entity): self
    {
        $response = new self();
        $response->id = $entity->getId();
        $response->nome = $entity->getNome();
        $response->tipo = $entity->getTipo()->value;
        $response->raca = $entity->getRaca();
        $response->peso = $entity->getPeso();
        $response->dataNascimento = $entity->getDataNascimento()->format('Y-m-d');
        $response->fotoUrl = $entity->getFotoUrl();

        return $response;
    }
}
