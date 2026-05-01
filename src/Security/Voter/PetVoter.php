<?php

namespace App\Security\Voter;

use App\Entity\Pet;
use App\Entity\User;
use App\Repository\ClienteRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'VIEW'|'EDIT'|'DELETE', Pet>
 */
class PetVoter extends Voter
{
    public function __construct(
        private readonly ClienteRepository $clienteRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof Pet;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $pet = $subject;
        $cliente = $this->clienteRepository->findOneByUserId($user->getId());

        if ($cliente === null) {
            return false;
        }

        return $pet->getCliente()->getId() === $cliente->getId();
    }
}
