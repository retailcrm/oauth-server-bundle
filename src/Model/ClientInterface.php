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

    /**
     * @param array<int, string> $redirectUris
     */
    public function setRedirectUris(array $redirectUris): self;

    /**
     * @return array<int, string>
     */
    public function getRedirectUris(): array;

    /**
     * @param array<int, string> $grantTypes
     */
    public function setGrantTypes(array $grantTypes): self;

    /**
     * @return array<int, string>
     */
    public function getGrantTypes(): array;

    public function checkGrantType(string $grantType): bool;

    public function checkSecret(?string $secret): bool;
}
