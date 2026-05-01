<?php

namespace App\Dto\Request;

use Symfony\Component\HttpFoundation\Request;

class LoginRequest
{
    public string $email;
    public string $password;

    public static function fromRequest(?Request $request = null): self
    {
        $dto = new self();

        if ($request !== null) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        if (empty($data['email'])) {
            throw new \InvalidArgumentException("O campo 'email' é obrigatório.");
        }
        if (empty($data['password'])) {
            throw new \InvalidArgumentException("O campo 'password' é obrigatório.");
        }

        $dto->email = $data['email'];
        $dto->password = $data['password'];

        return $dto;
    }
}
