<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use Symfony\Component\Security\Core\User\UserInterface;

class Grant
{
    public function __construct(
        private readonly ?UserInterface $user,
        private readonly ?string $scope = null,
        private readonly bool $issueRefreshToken = true,
    ) {
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function issueRefreshToken(): bool
    {
        return $this->issueRefreshToken;
    }
}
