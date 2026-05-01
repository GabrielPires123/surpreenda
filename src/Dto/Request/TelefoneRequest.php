<?php

namespace App\Dto\Request;

use Symfony\Component\HttpFoundation\Request;

class TelefoneRequest
{
    public string $numero;

    public static function fromRequest(?Request $request = null): self
    {
        $dto = new self();

        if ($request !== null) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        if (empty($data['numero'])) {
            throw new \InvalidArgumentException("O campo 'numero' é obrigatório.");
        }

        $dto->numero = $data['numero'];

        return $dto;
    }
}
