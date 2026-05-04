<?php

namespace App\Service;

use App\Entity\Cliente;
use App\Entity\Endereco;
use App\Entity\Telefone;
use App\Entity\User;
use App\Repository\Interface\ClienteRepositoryInterface;
use App\Repository\Interface\EnderecoRepositoryInterface;
use App\Repository\Interface\TelefoneRepositoryInterface;
use App\Service\Interface\ClienteServiceInterface;
use App\Validator\EntityValidator;

class ClienteService implements ClienteServiceInterface
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clienteRepository,
        private readonly EnderecoRepositoryInterface $enderecoRepository,
        private readonly TelefoneRepositoryInterface $telefoneRepository,
        private readonly EntityValidator $entityValidator,
    ) {
    }

    /**
     * Repository: Get cliente by user ID
     */
    public function getClienteByUserId(int $userId): ?Cliente
    {
        return $this->clienteRepository->findOneByUserId($userId);
    }

    /**
     * Repository: Get cliente by ID
     */
    public function getClienteOrThrow(string $clienteId): Cliente
    {
        $cliente = $this->clienteRepository->find($clienteId);
        if ($cliente === null) {
            throw new \InvalidArgumentException('Cliente não encontrado.');
        }

        return $cliente;
    }

    /**
     * Business: Update cliente profile
     */
    public function updateCliente(Cliente $cliente, array $data): Cliente
    {
        if (isset($data['cpf'])) {
            $cliente->setCpf($data['cpf']);
        }

        $user = $cliente->getUser();
        if (isset($data['nome'])) {
            $user->setNome($data['nome']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        $this->entityValidator->validateUser($user);
        $this->entityValidator->validateCliente($cliente);

        $this->clienteRepository->save($cliente, true);

        return $cliente;
    }

    /**
     * Repository: List all enderecos for a user
     */
    public function listEnderecosByUserId(int $userId): array
    {
        $cliente = $this->getClienteOrThrow($userId);

        return $this->enderecoRepository->findByClienteId($cliente->getId());
    }

    /**
     * Repository: Find endereco by ID
     */
    public function getEnderecoOrThrow(string $enderecoId): Endereco
    {
        $endereco = $this->enderecoRepository->find($enderecoId);
        if ($endereco === null) {
            throw new \InvalidArgumentException('Endereço não encontrado.');
        }

        return $endereco;
    }

    /**
     * Repository: Create endereco
     */
    public function createEndereco(int $userId, string $logradouro, string $numero, string $bairro, string $cidade, string $estado, string $cep, ?string $complemento = null): Endereco
    {
        $cliente = $this->getClienteOrThrow($userId);

        $endereco = (new Endereco())
            ->setLogradouro($logradouro)
            ->setNumero($numero)
            ->setComplemento($complemento)
            ->setBairro($bairro)
            ->setCidade($cidade)
            ->setEstado($estado)
            ->setCep($cep)
            ->setCliente($cliente)
            ->initUuid();

        $this->entityValidator->validateEndereco($endereco);

        $this->enderecoRepository->save($endereco, true);

        return $endereco;
    }

    /**
     * Repository: Update endereco
     */
    public function updateEndereco(string $enderecoId, array $data): Endereco
    {
        $endereco = $this->getEnderecoOrThrow($enderecoId);

        if (isset($data['logradouro'])) {
            $endereco->setLogradouro($data['logradouro']);
        }
        if (isset($data['numero'])) {
            $endereco->setNumero($data['numero']);
        }
        if (isset($data['complemento'])) {
            $endereco->setComplemento($data['complemento']);
        }
        if (isset($data['bairro'])) {
            $endereco->setBairro($data['bairro']);
        }
        if (isset($data['cidade'])) {
            $endereco->setCidade($data['cidade']);
        }
        if (isset($data['estado'])) {
            $endereco->setEstado($data['estado']);
        }
        if (isset($data['cep'])) {
            $endereco->setCep($data['cep']);
        }

        $this->entityValidator->validateEndereco($endereco);

        $this->enderecoRepository->save($endereco, true);

        return $endereco;
    }

    /**
     * Domain: Get first name
     */
    public function getPrimeiroNome(Cliente $cliente): string
    {
        return explode(' ', $cliente->getUser()->getFirstName())[0];
    }

    /**
     * Domain: Get full name
     */
    public function getNomeCompleto(Cliente $cliente): string
    {
        return trim($cliente->getUser()->getFirstName() . ' ' . $cliente->getUser()->getLastName());
    }

    /**
     * Domain: Get main address
     */
    public function getEnderecoPrincipal(Cliente $cliente): ?Endereco
    {
        foreach ($cliente->getEnderecos() as $endereco) {
            if ($endereco->isPrincipal()) {
                return $endereco;
            }
        }
        return $cliente->getEnderecos()->first() ?: null;
    }

    /**
     * Domain: Get main phone (mobile)
     */
    public function getTelefonePrincipal(Cliente $cliente): ?Telefone
    {
        foreach ($cliente->getTelefones() as $telefone) {
            if ($telefone->getTipo() === 'mobile') {
                return $telefone;
            }
        }
        return $cliente->getTelefones()->first() ?: null;
    }

    /**
     * Domain: Format CPF for display
     */
    public function formatarCpf(?string $cpf): string
    {
        if ($cpf === null) {
            return '';
        }

        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    /**
     * Domain: Validate CPF
     */
    public function validarCpf(?string $cpf): bool
    {
        if ($cpf === null) {
            return false;
        }

        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validate first digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $firstDigit = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int) $cpf[9] !== $firstDigit) {
            return false;
        }

        // Validate second digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $secondDigit = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int) $cpf[10] !== $secondDigit) {
            return false;
        }

        return true;
    }

    /**
     * Repository: List all telefones for a user
     */
    public function listTelefonesByUserId(int $userId): array
    {
        $cliente = $this->getClienteOrThrow($userId);

        return $this->telefoneRepository->findByClienteId($cliente->getId());
    }

    /**
     * Repository: Find telefone by ID
     */
    public function getTelefoneOrThrow(string $telefoneId): Telefone
    {
        $telefone = $this->telefoneRepository->find($telefoneId);
        if ($telefone === null) {
            throw new \InvalidArgumentException('Telefone não encontrado.');
        }

        return $telefone;
    }

    /**
     * Repository: Create telefone
     */
    public function createTelefone(int $userId, string $numero): Telefone
    {
        $cliente = $this->getClienteOrThrow($userId);

        $telefone = (new Telefone())
            ->setNumero($numero)
            ->setCliente($cliente)
            ->initUuid();

        $this->entityValidator->validateTelefone($telefone);

        $this->telefoneRepository->save($telefone, true);

        return $telefone;
    }

    /**
     * Repository: Delete telefone
     */
    public function deleteTelefone(string $telefoneId): void
    {
        $telefone = $this->getTelefoneOrThrow($telefoneId);
        $this->telefoneRepository->remove($telefone, true);
    }

    /**
     * Repository: Delete endereco
     */
    public function deleteEndereco(string $enderecoId): void
    {
        $endereco = $this->getEnderecoOrThrow($enderecoId);
        $this->enderecoRepository->remove($endereco, true);
    }

    /**
     * Repository: Update telefone
     */
    public function updateTelefone(string $telefoneId, array $data): Telefone
    {
        $telefone = $this->getTelefoneOrThrow($telefoneId);

        if (isset($data['numero'])) {
            $telefone->setNumero($data['numero']);
        }

        $this->entityValidator->validateTelefone($telefone);
        $this->telefoneRepository->save($telefone, true);

        return $telefone;
    }
}
