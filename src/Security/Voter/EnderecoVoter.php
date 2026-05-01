<?php

namespace App\Security\Voter;

use App\Entity\Endereco;
use App\Entity\User;
use App\Repository\ClienteRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<'VIEW'|'EDIT'|'DELETE', Endereco>
 */
class EnderecoVoter extends Voter
{
    public function __construct(
        private readonly ClienteRepository $clienteRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof Endereco;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $endereco = $subject;
        $cliente = $this->clienteRepository->findOneByUserId($user->getId());

        if ($cliente === null) {
            return false;
        }

        return $endereco->getCliente()->getId() === $cliente->getId();
    }
}
