<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\User;

interface AuthServiceInterface
{
    public function registerUser(string $nome, string $email, string $password, ?string $cpf = null, ?string $telefone = null, ?array $endereco = null): User;
    public function login(string $email, string $password): User;
    public function getUserAndCliente(int $userId): array;
}
