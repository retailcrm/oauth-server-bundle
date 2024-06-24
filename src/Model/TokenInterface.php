<?php

declare(strict_types=1);

namespace OAuth\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface TokenInterface
{
    public function setUser(?UserInterface $user): self;

    public function getUser(): ?UserInterface;

    public function setClient(ClientInterface $client): self;

    public function getClient(): ClientInterface;

    public function setExpiresAt(?int $expiresAt): self;

    public function getExpiresAt(): ?int;

    public function getExpiresIn(): ?int;

    public function hasExpired(): bool;

    public function setToken(string $token): self;

    public function getToken(): string;

    public function setScope(?string $scope): self;

    public function getScope(): ?string;
}
