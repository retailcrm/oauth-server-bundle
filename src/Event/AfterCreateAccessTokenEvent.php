<?php

declare(strict_types=1);

namespace OAuth\Event;

use OAuth\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AfterCreateAccessTokenEvent
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ?UserInterface $user,
        private readonly string $accessToken,
        private readonly ?string $refreshToken,
    ) {
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }
}
