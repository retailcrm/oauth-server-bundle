<?php

declare(strict_types=1);

namespace OAuth\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface ClientInterface extends UserInterface
{
    public function setPublicId(string $publicId): self;

    public function getPublicId(): string;

    public function setSecret(?string $secret): self;

    public function getSecret(): ?string;

    public function setRedirectUris(array $redirectUris): self;

    public function getRedirectUris(): array;

    public function setGrantTypes(array $grantTypes): self;

    public function getGrantTypes(): array;

    public function checkGrantType(string $grantType): bool;

    public function checkSecret(?string $secret): bool;
}
