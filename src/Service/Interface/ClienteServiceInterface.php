<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Cliente;
use App\Entity\Endereco;
use App\Entity\Telefone;

interface ClienteServiceInterface
{
    public function getClienteByUserId(int $userId): ?Cliente;
    public function getClienteOrThrow(string $clienteId): Cliente;
    public function updateCliente(Cliente $cliente, array $data): Cliente;
    public function listEnderecosByUserId(int $userId): array;
    public function getEnderecoOrThrow(string $enderecoId): Endereco;
    public function createEndereco(int $userId, string $logradouro, string $numero, string $bairro, string $cidade, string $estado, string $cep, ?string $complemento = null): Endereco;
    public function updateEndereco(string $enderecoId, array $data): Endereco;
    public function getPrimeiroNome(Cliente $cliente): string;
    public function getNomeCompleto(Cliente $cliente): string;
    public function getEnderecoPrincipal(Cliente $cliente): ?Endereco;
    public function getTelefonePrincipal(Cliente $cliente): ?Telefone;
    public function formatarCpf(?string $cpf): string;
    public function validarCpf(?string $cpf): bool;
    public function listTelefonesByUserId(int $userId): array;
    public function getTelefoneOrThrow(string $telefoneId): Telefone;
    public function createTelefone(int $userId, string $numero): Telefone;
    public function deleteTelefone(string $telefoneId): void;
    public function deleteEndereco(string $enderecoId): void;
    public function updateTelefone(string $telefoneId, array $data): Telefone;
}
