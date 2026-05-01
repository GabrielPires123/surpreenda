<?php

namespace App\Dto\Request;

use Symfony\Component\HttpFoundation\Request;

class CheckoutRequest
{
    public string $kitId;
    public string $petId;

    public static function fromRequest(?Request $request = null): self
    {
        $dto = new self();

        if ($request !== null) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        if (empty($data['kitId'])) {
            throw new \InvalidArgumentException("O campo 'kitId' é obrigatório.");
        }
        if (empty($data['petId'])) {
            throw new \InvalidArgumentException("O campo 'petId' é obrigatório.");
        }

        $dto->kitId = $data['kitId'];
        $dto->petId = $data['petId'];

        return $dto;
    }
}
