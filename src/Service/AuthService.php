<?php

namespace App\Service;

use App\Entity\Cliente;
use App\Entity\Endereco;
use App\Entity\Telefone;
use App\Entity\User;
use App\Repository\Interface\ClienteRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Service\Interface\AuthServiceInterface;
use App\Validator\EntityValidator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ClienteRepositoryInterface $clienteRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityValidator $entityValidator,
    ) {
    }

    public function registerUser(string $nome, string $email, string $cpf, string $password, ?string $telefone = null, ?array $endereco = null): User
    {
        $existingUser = $this->userRepository->findOneByEmail($email);
        if ($existingUser !== null) {
            throw new \InvalidArgumentException('E-mail já cadastrado.');
        }

        $existingCliente = $this->clienteRepository->findOneByCpf($cpf);
        if ($existingCliente !== null) {
            throw new \InvalidArgumentException('CPF já cadastrado.');
        }

        $user = (new User())
            ->setEmail($email)
            ->setNome($nome)
            ->setIsVerified(false);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $user->initUuid();

        $cliente = (new Cliente())
            ->setCpf($cpf)
            ->setUser($user);

        if ($telefone !== null) {
            $telefoneEntity = (new Telefone())
                ->setNumero($telefone)
                ->setCliente($cliente)
                ->initUuid();
            $cliente->addTelefone($telefoneEntity);
        }
        if ($endereco !== null) {
            $enderecoEntity = (new Endereco())
                ->setLogradouro($endereco['logradouro'] ?? '')
                ->setNumero($endereco['numero'] ?? '')
                ->setComplemento($endereco['complemento'] ?? null)
                ->setBairro($endereco['bairro'] ?? '')
                ->setCidade($endereco['cidade'] ?? '')
                ->setEstado($endereco['estado'] ?? '')
                ->setCep($endereco['cep'] ?? '')
                ->setCliente($cliente)
                ->initUuid();
            $cliente->addEndereco($enderecoEntity);
        }

        $this->entityValidator->validateCliente($cliente);

        $this->userRepository->save($user, true);
        $this->clienteRepository->save($cliente, true);

        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findOneByEmail($email);
        if ($user === null) {
            throw new \InvalidArgumentException('Credenciais inválidas.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new \InvalidArgumentException('Credenciais inválidas.');
        }

        return $user;
    }

    public function getUserAndCliente(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        $cliente = $this->clienteRepository->findOneByUserId($userId);

        return [$user, $cliente];
    }
}
