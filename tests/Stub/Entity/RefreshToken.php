<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Entity;

use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshToken as BaseRefreshToken;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshToken extends BaseRefreshToken
{
    protected string $token = '';
    protected ?UserInterface $user = null;
    protected ?int $expiresAt = null;
    protected ?string $scope = null;

    public function __construct(
        protected ClientInterface $client,
    ) {
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function setExpiresAt(?int $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setScope(?string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
