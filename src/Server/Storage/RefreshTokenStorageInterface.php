<?php

declare(strict_types=1);

namespace OAuth\Server\Storage;

use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshTokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface RefreshTokenStorageInterface extends DeleteExpiredStorageInterface
{
    public function getRefreshToken(string $refreshToken): ?RefreshTokenInterface;

    public function createRefreshToken(string $refreshToken, ClientInterface $client, ?UserInterface $user, int $expires, ?string $scope = null): void;

    public function unsetRefreshToken(string $refreshToken): void;
}
