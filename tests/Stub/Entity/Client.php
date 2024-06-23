<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Entity;

use OAuth\Model\Client as BaseClient;

class Client extends BaseClient
{
    protected string $randomId;
    protected ?string $secret = null;
    protected array $redirectUris = [];
    protected array $allowedGrantTypes = [];

    public function __construct(string $publicId)
    {
        $this->randomId = $publicId;
    }

    public function getPublicId(): string
    {
        return $this->randomId;
    }

    public function setSecret(?string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setRedirectUris(array $redirectUris): self
    {
        $this->redirectUris = $redirectUris;

        return $this;
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function setGrantTypes(array $grantTypes): self
    {
        $this->allowedGrantTypes = $grantTypes;

        return $this;
    }

    public function getGrantTypes(): array
    {
        return $this->allowedGrantTypes;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->randomId;
    }
}
