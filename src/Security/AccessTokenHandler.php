<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly UserProviderInterface $userProvider,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        if (empty($accessToken)) {
            throw new \InvalidArgumentException('Empty access token');
        }

        $email = $this->extractEmailFromToken($accessToken);

        if (!$email) {
            throw new \InvalidArgumentException('Invalid access token');
        }

        return new UserBadge($email, function ($userIdentifier) {
            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        });
    }

    private function extractEmailFromToken(string $token): ?string
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        try {
            $payload = json_decode(
                base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])),
                true
            );

            return $payload['email'] ?? null;
        } catch (\Exception) {
            return null;
        }
    }
}
