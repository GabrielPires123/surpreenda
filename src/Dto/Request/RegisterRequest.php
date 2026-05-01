<?php

namespace App\Dto\Request;

use Symfony\Component\HttpFoundation\Request;

class RegisterRequest
{
    public string $nome;
    public string $cpf;
    public string $email;
    public string $password;
    public ?string $telefone = null;
    public ?array $endereco = null;

    public static function fromRequest(?Request $request = null): self
    {
        $dto = new self();

        if ($request !== null) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        $requiredFields = ['nome', 'cpf', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("O campo '{$field}' é obrigatório.");
            }
        }

        $dto->nome = $data['nome'];
        $dto->cpf = $data['cpf'];
        $dto->email = $data['email'];
        $dto->password = $data['password'];
        $dto->telefone = $data['telefone'] ?? null;
        $dto->endereco = $data['endereco'] ?? null;

        return $dto;
    }
}
