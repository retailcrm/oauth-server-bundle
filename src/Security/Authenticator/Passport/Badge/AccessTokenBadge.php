<?php

declare(strict_types=1);

namespace OAuth\Security\Authenticator\Passport\Badge;

use OAuth\Model\AccessTokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class AccessTokenBadge implements BadgeInterface
{
    public function __construct(
        private readonly AccessTokenInterface $accessToken,
        private readonly array $roles
    ) {
    }

    public function isResolved(): bool
    {
        return !empty($this->roles);
    }

    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
