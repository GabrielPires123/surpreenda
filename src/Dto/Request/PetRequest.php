<?php

namespace App\Dto\Request;

use App\Enum\PetType;
use Symfony\Component\HttpFoundation\Request;

class PetRequest
{
    public string $nome;
    public PetType $tipo;
    public ?string $raca = null;
    public ?float $peso = null;
    public string $dataNascimento;

    public static function fromRequest(?Request $request = null): self
    {
        $dto = new self();

        if ($request !== null) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        if (empty($data['nome'])) {
            throw new \InvalidArgumentException("O campo 'nome' é obrigatório.");
        }
        if (empty($data['tipo'])) {
            throw new \InvalidArgumentException("O campo 'tipo' é obrigatório.");
        }
        if (empty($data['dataNascimento'])) {
            throw new \InvalidArgumentException("O campo 'dataNascimento' é obrigatório.");
        }

        try {
            $dto->tipo = PetType::from($data['tipo']);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Tipo inválido: {$data['tipo']}");
        }

        $dto->nome = $data['nome'];
        $dto->raca = $data['raca'] ?? null;
        $dto->peso = isset($data['peso']) ? (float) $data['peso'] : null;
        $dto->dataNascimento = $data['dataNascimento'];

        return $dto;
    }
}
